<?php declare(strict_types=1);

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
use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\Where;
use WyriHaximus\React\SimpleORM\Query\Where\Expression;
use WyriHaximus\React\SimpleORM\Query\Where\Field;
use function array_key_exists;
use function array_values;
use function date;
use function explode;
use function is_scalar;
use function is_string;
use function Latitude\QueryBuilder\alias;
use function Latitude\QueryBuilder\field;
use function Latitude\QueryBuilder\func;
use function Latitude\QueryBuilder\on;
use function Safe\substr;
use function spl_object_hash;
use function strpos;
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

    public function count(): PromiseInterface
    {
        return $this->client->query(
            $this->queryFactory->select(alias(func('COUNT', '*'), 'count'))->from($this->entity->getTable())->asExpression()
        )->take(self::SINGLE)->toPromise()->then(static function (array $row): int {
            return (int) $row['count'];
        });
    }

    public function page(int $page, ?Where $where = null, ?Order $order = null, int $perPage = RepositoryInterface::DEFAULT_PER_PAGE): Observable
    {
        $query = $this->buildSelectQuery($where ?? new Where(), $order ?? new Order());
        $query = $query->limit($perPage)->offset(--$page * $perPage);

        return $this->fetchAndHydrate($query);
    }

    public function fetch(?Where $where = null, ?Order $order = null, int $limit = ZERO): Observable
    {
        $query = $this->buildSelectQuery($where ?? new Where(), $order ?? new Order());
        if ($limit > ZERO) {
            $query = $query->limit($limit)->offset(ZERO);
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
            $this->queryFactory->insert($this->entity->getTable(), $fields)->asExpression()
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
            $this->queryFactory->update($this->entity->getTable(), $fields)->
            where(field('id')->eq($entity->getId()))->asExpression()
        )->toPromise()->then(function () use ($entity): PromiseInterface {
            return $this->fetch(new Where(
                new Where\Field('id', 'eq', [$entity->getId()]),
            ), new Order(), ONE)->toPromise();
        });
    }

    private function buildSelectQuery(Where $constraints, Order $order): SelectQuery
    {
        $query = $this->buildBaseSelectQuery();

        $query = $query->columns(...array_values($this->fields));

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

        foreach ($order->orders() as $by) {
            $field = $this->translateFieldName($by->field());
            $query = $query->orderBy($field, $by->order());
        }

        return $query;
    }

    private function buildBaseSelectQuery(): SelectQuery
    {
        $i                             = ZERO;
        $tableKey                      = spl_object_hash($this->entity) . '___root';
        $this->tableAliases[$tableKey] = 't' . $i++;
        $query                         = $this->queryFactory->select()->from(alias($this->entity->getTable(), $this->tableAliases[$tableKey]));

        foreach ($this->entity->getFields() as $field) {
            $this->fields[$this->tableAliases[$tableKey] . '___' . $field->getName()] = alias($this->tableAliases[$tableKey] . '.' . $field->getName(), $this->tableAliases[$tableKey] . '___' . $field->getName());
        }

        $query = $this->buildJoins($query, $this->entity, $i);

        return $query;
    }

    private function buildJoins(SelectQuery $query, InspectedEntityInterface $entity, int &$i, string $rootProperty = 'root'): SelectQuery
    {
        foreach ($entity->getJoins() as $join) {
            if ($join->getType() !== 'inner') {
                continue;
            }

            if ($join->getLazy() === JoinInterface::IS_LAZY) {
                continue;
            }

            if ($entity->getClass() === $join->getEntity()->getClass()) {
                continue;
            }

            $tableKey = spl_object_hash($join->getEntity()) . '___' . $join->getProperty();
            if (! array_key_exists($tableKey, $this->tableAliases)) {
                $this->tableAliases[$tableKey] = 't' . $i++;
            }

            $clauses = null;
            foreach ($join->getClause() as $clause) {
                $onLeftSide = $this->tableAliases[$tableKey] . '.' . $clause->getForeignKey();
                if ($clause->getForeignFunction() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onLeftSide = $clause->getForeignFunction() . '(' . $onLeftSide . ')';
                }

                if ($clause->getForeignCast() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $clause->getForeignCast() . ')';
                }

                $onRightSide =
                    $this->tableAliases[spl_object_hash($entity) . '___' . $rootProperty] . '.' . $clause->getLocalKey();
                if ($clause->getLocalFunction() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onRightSide = $clause->getLocalFunction() . '(' . $onRightSide . ')';
                }

                if ($clause->getLocalCast() !== null) {
                    /** @psalm-suppress PossiblyNullOperand */
                    $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $clause->getLocalCast() . ')';
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
                        $join->getEntity()->getTable(),
                        $this->tableAliases[$tableKey]
                    ),
                    $clauses
                );
            }

            foreach ($join->getEntity()->getFields() as $field) {
                $this->fields[$this->tableAliases[$tableKey] . '___' . $field->getName()] = alias($this->tableAliases[$tableKey] . '.' . $field->getName(), $this->tableAliases[$tableKey] . '___' . $field->getName());
            }

            unset($this->fields[$entity->getTable() . '___' . $join->getProperty()]);

            $query = $this->buildJoins($query, $join->getEntity(), $i, $join->getProperty());
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

        foreach ($entity->getJoins() as $join) {
            if ($join->getType() === 'inner' && $entity->getClass() !== $join->getEntity()->getClass() && $join->getLazy() === false) {
                $tree[$join->getProperty()] = $this->buildTree($row, $join->getEntity(), $join->getProperty());

                continue;
            }

            if ($join->getType() === 'inner' && ($join->getLazy() === JoinInterface::IS_LAZY || $entity->getClass() === $join->getEntity()->getClass())) {
                $tree[$join->getProperty()] = new LazyPromise(function () use ($row, $join, $tableKey): PromiseInterface {
                    return new Promise(function (callable $resolve, callable $reject) use ($row, $join, $tableKey): void {
                        foreach ($join->getClause() as $clause) {
                            if ($row[$this->tableAliases[$tableKey]][$clause->getLocalKey()] === null) {
                                $resolve(null);

                                return;
                            }
                        }

                        $where = [];

                        foreach ($join->getClause() as $clause) {
                            $onLeftSide = $clause->getForeignKey();
                            if ($clause->getForeignFunction() !== null) {
                                /** @psalm-suppress PossiblyNullArgument */
                                $onLeftSide = func($clause->getForeignFunction(), $onLeftSide);
                            }

                            if ($clause->getForeignCast() !== null) {
                                /** @psalm-suppress PossiblyNullArgument */
                                $onLeftSide = alias(func('CAST', $onLeftSide), $clause->getForeignCast());
                            }

                            if (is_string($onLeftSide)) {
                                $where[] = new Where\Field(
                                    $onLeftSide,
                                    'eq',
                                    [
                                        $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                                    ]
                                );
                            } else {
                                $where[] = new Where\Expression(
                                    $onLeftSide,
                                    'eq',
                                    [
                                        $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                                    ]
                                );
                            }
                        }

                        $this->client
                            ->getRepository($join->getEntity()
                            ->getClass())
                            ->fetch(new Where(...$where), new Order(), self::SINGLE)
                            ->toPromise()
                            ->then($resolve, $reject);
                    });
                });

                continue;
            }

            $tree[$join->getProperty()] = Observable::defer(
                function () use ($row, $join, $tableKey): Observable {
                    $where = [];

                    foreach ($join->getClause() as $clause) {
                        $where[] = new Where\Field(
                            $clause->getForeignKey(),
                            'eq',
                            [
                                $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                            ]
                        );
                    }

                    return $this->client->getRepository($join->getEntity()->getClass())->fetch(new Where(...$where));
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
