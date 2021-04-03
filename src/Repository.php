<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use DateTimeInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\Query\SelectQuery;
use Latitude\QueryBuilder\QueryFactory;
use Latitude\QueryBuilder\QueryInterface;
use Ramsey\Uuid\Uuid;
use React\Promise\LazyPromise;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;
use Safe\DateTimeImmutable;
use WyriHaximus\React\SimpleORM\Annotation\JoinInterface;
use WyriHaximus\React\SimpleORM\Query\GroupBy;
use WyriHaximus\React\SimpleORM\Query\Limit;
use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\SectionInterface;
use WyriHaximus\React\SimpleORM\Query\Where;
use WyriHaximus\React\SimpleORM\Query\Where\Expression;
use WyriHaximus\React\SimpleORM\Query\Where\Field;

use function array_key_exists;
use function array_map;
use function array_values;
use function explode;
use function is_scalar;
use function is_string;
use function Latitude\QueryBuilder\alias;
use function Latitude\QueryBuilder\field;
use function Latitude\QueryBuilder\func;
use function Latitude\QueryBuilder\on;
use function Safe\date;
use function Safe\substr;
use function spl_object_hash;
use function strpos;
use function WyriHaximus\iteratorOrArrayToArray;

use const WyriHaximus\Constants\Boolean\TRUE_;
use const WyriHaximus\Constants\Numeric\ONE;
use const WyriHaximus\Constants\Numeric\ZERO;

final class Repository implements RepositoryInterface
{
    private const DATE_TIME_TIMEZONE_FORMAT = 'Y-m-d H:i:s e';
    private const SINGLE                    = ONE;

    private InspectedEntityInterface $entity;

    private ClientInterface $client;

    private QueryFactory $queryFactory;

    private Hydrator $hydrator;

    /** @var ExpressionInterface[] */
    private array $fields = [];

    /** @var string[] */
    private array $tableAliases = [];

    public function __construct(InspectedEntityInterface $entity, ClientInterface $client, QueryFactory $queryFactory)
    {
        $this->entity       = $entity;
        $this->client       = $client;
        $this->queryFactory = $queryFactory;
        $this->hydrator     = new Hydrator();
    }

    /** @phpstan-ignore-next-line */
    public function count(?Where $where = null): PromiseInterface
    {
        $query = $this->queryFactory->select(alias(func('COUNT', '*'), 'count'))->from(alias($this->entity->table(), 't0'));
        if ($where instanceof Where) {
            $query = $this->applyWhereToQuery($where, $query);
        }

        return $this->client->query(
            $query->asExpression()
        )->take(self::SINGLE)->toPromise()->then(static function (array $row): int {
            return (int) $row['count'];
        });
    }

    /** @phpstan-ignore-next-line */
    public function page(int $page, ?Where $where = null, ?Order $order = null, int $perPage = RepositoryInterface::DEFAULT_PER_PAGE): Observable
    {
        $query = $this->buildSelectQuery($where ?? new Where(), $order ?? new Order());
        $query = $query->limit($perPage)->offset(--$page * $perPage);

        return $this->fetchAndHydrate($query);
    }

    /**
     * @param array<SectionInterface> $sections
     *
     * @phpstan-ignore-next-line
     */
    public function fetch(SectionInterface ...$sections): Observable
    {
        $query = $this->buildSelectQuery(...$sections);
        foreach ($sections as $section) {
            if (! ($section instanceof Limit) || $section->limit() <= ZERO) {
                continue;
            }

            $query = $query->limit($section->limit())->offset(ZERO);
        }

        return $this->fetchAndHydrate($query);
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function create(array $fields): PromiseInterface
    {
        $id                 = Uuid::getFactory()->uuid4()->toString();
        $fields['id']       = $id;
        $fields['created']  = new DateTimeImmutable();
        $fields['modified'] = new DateTimeImmutable();

        $fields = $this->prepareFields($fields);

        return $this->client->query(
            $this->queryFactory->insert($this->entity->table(), $fields)->asExpression()
        )->toPromise()->then(function () use ($id): PromiseInterface {
            return $this->fetch(new Where(
                new Where\Field(
                    'id',
                    'eq',
                    [$id],
                ),
            ))->take(ONE)->toPromise();
        });
    }

    public function update(EntityInterface $entity): PromiseInterface
    {
        $fields             = $this->hydrator->extract($this->entity, $entity);
        $fields['modified'] = new DateTimeImmutable();
        $fields             = $this->prepareFields($fields);

        return $this->client->query(
            $this->queryFactory->update($this->entity->table(), $fields)->
            where(field('id')->eq($entity->id()))->asExpression()
        )->toPromise()->then(function () use ($entity): PromiseInterface {
            return $this->fetch(new Where(
                new Where\Field('id', 'eq', [$entity->id()]),
            ), new Limit(ONE))->toPromise();
        });
    }

    public function delete(EntityInterface $entity): PromiseInterface
    {
        return $this->client->query(
            $this->queryFactory->delete($this->entity->table())->
            where(field('id')->eq($entity->id()))->asExpression()
        )->toPromise();
    }

    /**
     * @param array<SectionInterface> $sections
     *
     * @phpstan-ignore-next-line
     */
    private function buildSelectQuery(SectionInterface ...$sections): SelectQuery
    {
        $query = $this->buildBaseSelectQuery();
        $query = $query->columns(...array_values($this->fields));
        foreach ($sections as $section) {
            /** @phpstan-ignore-next-line */
            switch (TRUE_) {
                case $section instanceof Where:
                    /** @psalm-suppress ArgumentTypeCoercion */
                    $query = $this->applyWhereToQuery($section, $query);
                    break;
                case $section instanceof Order:
                    /** @psalm-suppress UndefinedInterfaceMethod */
                    foreach ($section->orders() as $by) {
                        $field = $this->translateFieldName($by->field());
                        $query = $query->orderBy($field, $by->order());
                    }

                    break;
                case $section instanceof GroupBy:
                    /**
                     * @psalm-suppress ArgumentTypeCoercion
                     * @psalm-suppress UndefinedInterfaceMethod
                     */
                    $query = $query->groupBy(...array_map(
                        fn (string $column): string => $this->translateFieldName($column),
                        iteratorOrArrayToArray($section->columns())
                    ));
                    break;
            }
        }

        return $query;
    }

    private function applyWhereToQuery(Where $constraints, SelectQuery $query): SelectQuery
    {
        foreach ($constraints->wheres() as $i => $constraint) {
            if ($constraint instanceof Expression) {
                $where = $constraint->expression();
                $where = $constraint->applyExpression($where);
            } elseif ($constraint instanceof Field) {
                $where = field($this->translateFieldName($constraint->field()));
                $where = $constraint->applyCriteria($where);
            } else {
                continue;
            }

            if ($i === ZERO) {
                $query = $query->where($where);
                continue;
            }

            $query = $query->andWhere($where);
        }

        return $query;
    }

    private function buildBaseSelectQuery(): SelectQuery
    {
        $i                             = ZERO;
        $tableKey                      = spl_object_hash($this->entity) . '___root';
        $this->tableAliases[$tableKey] = 't' . $i++;
        $query                         = $this->queryFactory->select()->from(alias($this->entity->table(), $this->tableAliases[$tableKey]));

        foreach ($this->entity->fields() as $field) {
            $this->fields[$this->tableAliases[$tableKey] . '___' . $field->name()] = alias($this->tableAliases[$tableKey] . '.' . $field->name(), $this->tableAliases[$tableKey] . '___' . $field->name());
        }

        $query = $this->buildJoins($query, $this->entity, $i);

        return $query;
    }

    private function buildJoins(SelectQuery $query, InspectedEntityInterface $entity, int &$i, string $rootProperty = 'root'): SelectQuery
    {
        foreach ($entity->joins() as $join) {
            if ($join->type() !== 'inner') {
                continue;
            }

            if ($join->lazy() === JoinInterface::IS_LAZY) {
                continue;
            }

            if ($entity->class() === $join->entity()->class()) {
                continue;
            }

            $tableKey = spl_object_hash($join->entity()) . '___' . $join->property();
            if (! array_key_exists($tableKey, $this->tableAliases)) {
                $this->tableAliases[$tableKey] = 't' . $i++;
            }

            $clauses = null;
            foreach ($join->clause() as $clause) {
                $onLeftSide = $this->tableAliases[$tableKey] . '.' . $clause->foreignKey();
                if ($clause->foreignFunction() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onLeftSide = $clause->foreignFunction() . '(' . $onLeftSide . ')';
                }

                if ($clause->foreignCast() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $clause->foreignCast() . ')';
                }

                $onRightSide =
                    $this->tableAliases[spl_object_hash($entity) . '___' . $rootProperty] . '.' . $clause->localKey();
                if ($clause->localFunction() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onRightSide = $clause->localFunction() . '(' . $onRightSide . ')';
                }

                if ($clause->localCast() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $clause->localCast() . ')';
                }

                if ($clauses === null) {
                    $clauses = on($onLeftSide, $onRightSide);

                    continue;
                }

                $clauses = on($onLeftSide, $onRightSide)->and($clauses);
            }

            if ($clauses !== null) {
                /** @psalm-suppress PossiblyNullArgument */
                $query = $query->innerJoin(
                    alias(
                        $join->entity()->table(),
                        $this->tableAliases[$tableKey]
                    ),
                    $clauses
                );
            }

            foreach ($join->entity()->fields() as $field) {
                $this->fields[$this->tableAliases[$tableKey] . '___' . $field->name()] = alias($this->tableAliases[$tableKey] . '.' . $field->name(), $this->tableAliases[$tableKey] . '___' . $field->name());
            }

            unset($this->fields[$entity->table() . '___' . $join->property()]);

            $query = $this->buildJoins($query, $join->entity(), $i, $join->property());
        }

        return $query;
    }

    private function fetchAndHydrate(QueryInterface $query): Observable
    {
        return $this->client->query(
            $query->asExpression()
        )->map(function (array $row): array {
            return $this->inflate($row);
        })->map(function (array $row): array {
            return $this->buildTree($row, $this->entity);
        })->map(function (array $row): EntityInterface {
            return $this->hydrator->hydrate($this->entity, $row);
        });
    }

    /**
     * @param mixed[] $row
     *
     * @return mixed[]
     */
    private function inflate(array $row): array
    {
        $tables = [];

        foreach ($row as $key => $value) {
            [$table, $field]        = explode('___', $key);
            $tables[$table][$field] = $value;
        }

        return $tables;
    }

    /**
     * @param mixed[] $row
     *
     * @return mixed[]
     */
    private function buildTree(array $row, InspectedEntityInterface $entity, string $tableKeySuffix = 'root'): array
    {
        $tableKey = spl_object_hash($entity) . '___' . $tableKeySuffix;
        $tree     = $row[$this->tableAliases[$tableKey]];

        foreach ($entity->joins() as $join) {
            if ($join->type() === 'inner' && $entity->class() !== $join->entity()->class() && $join->lazy() === false) {
                $tree[$join->property()] = $this->buildTree($row, $join->entity(), $join->property());

                continue;
            }

            if ($join->type() === 'inner' && ($join->lazy() === JoinInterface::IS_LAZY || $entity->class() === $join->entity()->class())) {
                /**
                 * @phpstan-ignore-next-line
                 * @psalm-suppress DeprecatedClass
                 */
                $tree[$join->property()] = new LazyPromise(function () use ($row, $join, $tableKey): PromiseInterface {
                    return new Promise(function (callable $resolve, callable $reject) use ($row, $join, $tableKey): void {
                        foreach ($join->clause() as $clause) {
                            if ($row[$this->tableAliases[$tableKey]][$clause->localKey()] === null) {
                                $resolve(null);

                                return;
                            }
                        }

                        $where = [];

                        foreach ($join->clause() as $clause) {
                            $onLeftSide = $clause->foreignKey();
                            if ($clause->foreignFunction() !== null) {
                                /** @psalm-suppress PossiblyNullArgument */
                                $onLeftSide = func($clause->foreignFunction(), $onLeftSide);
                            }

                            if ($clause->foreignCast() !== null) {
                                /** @psalm-suppress PossiblyNullArgument */
                                $onLeftSide = alias(func('CAST', $onLeftSide), $clause->foreignCast());
                            }

                            if (is_string($onLeftSide)) {
                                $where[] = new Where\Field(
                                    $onLeftSide,
                                    'eq',
                                    [
                                        $row[$this->tableAliases[$tableKey]][$clause->localKey()],
                                    ]
                                );
                            } else {
                                $where[] = new Where\Expression(
                                    $onLeftSide,
                                    'eq',
                                    [
                                        $row[$this->tableAliases[$tableKey]][$clause->localKey()],
                                    ]
                                );
                            }
                        }

                        $this->client
                            ->repository($join->entity()
                            ->class())
                            ->fetch(new Where(...$where), new Limit(self::SINGLE))
                            ->toPromise()
                            ->then($resolve, $reject);
                    });
                });

                continue;
            }

            $tree[$join->property()] = Observable::defer(
                function () use ($row, $join, $tableKey): Observable {
                    $where = [];

                    foreach ($join->clause() as $clause) {
                        $where[] = new Where\Field(
                            $clause->foreignKey(),
                            'eq',
                            [
                                $row[$this->tableAliases[$tableKey]][$clause->localKey()],
                            ]
                        );
                    }

                    return $this->client->repository($join->entity()->class())->fetch(new Where(...$where));
                },
                new ImmediateScheduler()
            );
        }

        return $tree;
    }

    private function translateFieldName(string $name): string
    {
        $pos = strpos($name, '(');
        if ($pos === false) {
            return 't0.' . $name;
        }

        return substr($name, ZERO, $pos + ONE) . 't0.' . substr($name, $pos + ONE);
    }

    /**
     * @param mixed[] $fields
     *
     * @return mixed[]
     */
    private function prepareFields(array $fields): array
    {
        foreach ($fields as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $fields[$key] = $value = date(
                    self::DATE_TIME_TIMEZONE_FORMAT,
                    (int) $value->format('U')
                );
            }

            if (is_scalar($value)) {
                continue;
            }

            unset($fields[$key]);
        }

        return $fields;
    }
}
