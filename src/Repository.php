<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use DateTimeImmutable;
use DateTimeInterface;
use Latitude\QueryBuilder\CriteriaInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\Query\SelectQuery;
use Latitude\QueryBuilder\QueryFactory;
use Latitude\QueryBuilder\QueryInterface;
use Ramsey\Uuid\Uuid;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReflectionClass;
use RuntimeException;
use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;
use Throwable;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Query\Limit;
use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\SectionInterface;
use WyriHaximus\React\SimpleORM\Query\Where;
use WyriHaximus\React\SimpleORM\Query\Where\Expression;
use WyriHaximus\React\SimpleORM\Query\Where\Field;
use WyriHaximus\React\SimpleORM\Tools\IncrementingInteger;
use WyriHaximus\React\SimpleORM\Tools\LazyPromise;

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
use function spl_object_hash;
use function strpos;
use function substr;
use function WyriHaximus\React\awaitObservable;

/**
 * @template T of EntityInterface
 * @template-implements RepositoryInterface<T>
 */
final class Repository implements RepositoryInterface
{
    private const string DATE_TIME_TIMEZ1_FORMAT = 'Y-m-d H:i:s e';
    private const int SINGLE                     = 1;
    private const int STREAM_PER_PAGE            = 100;

    /** @var ExpressionInterface[] */
    private array $fields = [];

    /** @var string[] */
    private array $tableAliases = [];

    /** @param InspectedEntityInterface<T> $entity */
    public function __construct(
        private readonly InspectedEntityInterface $entity,
        private readonly ClientInterface $client,
        private readonly QueryFactory $queryFactory,
        private readonly Connection $connection,
        private readonly Hydrator $hydrator,
    ) {
    }

    /** @phpstan-ignore ergebnis.noParameterWithNullDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration */
    public function count(Where|null $where = null): int
    {
        $query = $this->queryFactory->select(alias(func('COUNT', '*'), 'count'))->from(alias($this->entity->table(), 't0'));
        if ($where instanceof Where) {
            $query = $this->applyWhereToQuery($where, $query);
        }

        foreach (
            $this->connection->query(
                $query->asExpression(),
            ) as $row
        ) {
            /** @phpstan-ignore cast.int */
            return (int) $row['count'];
        }

        throw new RuntimeException('Could not count rows');
    }

    /**
     * @return iterable<T>
     *
     * @phpstan-ignore ergebnis.noParameterWithNullDefaultValue,ergebnis.noParameterWithNullDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullableTypeDeclaration
     */
    public function page(int $page, Where|null $where = null, Order|null $order = null, int $perPage = RepositoryInterface::DEFAULT_PER_PAGE): iterable
    {
        $query = $this->buildSelectQuery($where ?? new Where(), $order ?? new Order());
        $query = $query->limit($perPage)->offset(--$page * $perPage);

        yield from $this->fetchAndHydrate($query);
    }

    /** @return iterable<T> */
    public function fetch(SectionInterface ...$sections): iterable
    {
        $query = $this->buildSelectQuery(...$sections);
        foreach ($sections as $section) {
            if (! ($section instanceof Limit) || $section->limit() <= 0) {
                continue;
            }

            $query = $query->limit($section->limit())->offset(0);
        }

        yield from $this->fetchAndHydrate($query);
    }

    /** @return T */
    public function first(SectionInterface ...$sections): EntityInterface
    {
        foreach ($this->fetch(...$sections) as $row) {
            return $row;
        }

        throw new RuntimeException('Could not find first item');
    }

    /** @return iterable<T> */
    public function stream(SectionInterface ...$sections): iterable
    {
        $query = $this->buildSelectQuery(...$sections);

        $offset = 0;
        do {
            $hasRows = false;

            $q = clone $query;
            foreach ($this->fetchAndHydrate($q->limit(self::STREAM_PER_PAGE)->offset($offset)) as $row) {
                $hasRows = true;

                yield $row;
            }

            $offset += self::STREAM_PER_PAGE;
        } while ($hasRows);
    }

    /**
     * @param array<string, mixed> $fields
     *
     * @return T
     */
    public function create(array $fields): EntityInterface
    {
        $id                 = Uuid::getFactory()->uuid4()->toString();
        $fields['id']       = $id;
        $fields['created']  = new DateTimeImmutable();
        $fields['modified'] = new DateTimeImmutable();

        $fields = $this->prepareFields($fields);

        foreach (
            $this->connection->query(
                $this->queryFactory->insert($this->entity->table(), $fields)->asExpression(),
            ) as $underscore
        ) {
            break;
        }

        foreach (
            $this->fetch(new Where(
                new Where\Field(
                    'id',
                    'eq',
                    [$id],
                ),
            )) as $item
        ) {
            return $item;
        }

        throw new RuntimeException('Could not create item');
    }

    /** @return T */
    public function update(EntityInterface $entity): EntityInterface
    {
        $fields             = $this->hydrator->extract($entity);
        $fields['modified'] = new DateTimeImmutable();
        $fields             = $this->prepareFields($fields);

        foreach (
            $this->connection->query(
                $this->queryFactory->update(
                    $this->entity->table(),
                    $fields,
                )->where(
                /** @phpstan-ignore property.notFound */
                    field('id')->eq($entity->id),
                )->asExpression(),
            ) as $underscore
        ) {
            break;
        }

        foreach (
            $this->fetch(new Where(
            /** @phpstan-ignore property.notFound */
                new Where\Field('id', 'eq', [$entity->id]),
            ), new Limit(1)) as $updatedEnitty
        ) {
            return $updatedEnitty;
        }

        throw new RuntimeException('Could not update item');
    }

    /** @param T $entity */
    public function delete(EntityInterface $entity): null
    {
        $this->connection->query(
            $this->queryFactory->delete(
                $this->entity->table(),
            )->where(
                /** @phpstan-ignore property.notFound */
                field('id')->eq($entity->id),
            )->asExpression(),
        );

        return null;
    }

    private function buildSelectQuery(SectionInterface ...$sections): SelectQuery
    {
        $query = $this->buildBaseSelectQuery();
        $query = $query->columns(...array_values($this->fields));
        foreach ($sections as $section) {
            /** @phpstan-ignore ergebnis.noSwitch */
            switch (true) {
                case $section instanceof Where:
                    $query = $this->applyWhereToQuery($section, $query);
                    break;
                case $section instanceof Order:
                    foreach ($section->orders() as $by) {
                        $field = $this->translateFieldName($by->field());
                        $query = $query->orderBy($field, $by->order());
                    }

                    break;
            }
        }

        return $query;
    }

    private function applyWhereToQuery(Where $constraints, SelectQuery $query): SelectQuery
    {
        foreach ($constraints->wheres() as $i => $constraint) {
            if ($constraint instanceof Expression) {
                $where = $constraint->applyExpression(
                    $constraint->expression(),
                );
            } elseif ($constraint instanceof Field) {
                $where = $constraint->applyCriteria(
                    field(
                        $this->translateFieldName(
                            $constraint->field(),
                        ),
                    ),
                );
            } else {
                continue;
            }

            if ($i === 0) {
                $query = $query->where($where);
                continue;
            }

            $query = $query->andWhere($where);
        }

        return $query;
    }

    private function buildBaseSelectQuery(): SelectQuery
    {
        $i                             = new IncrementingInteger();
        $tableKey                      = spl_object_hash($this->entity) . '___root';
        $this->tableAliases[$tableKey] = 't' . $i->getNext();
        $query                         = $this->queryFactory->select()->from(alias($this->entity->table(), $this->tableAliases[$tableKey]));

        foreach ($this->entity->fields() as $field) {
            $this->fields[$this->tableAliases[$tableKey] . '___' . $field->name] = alias(
                $this->tableAliases[$tableKey] . '.' . $field->name,
                $this->tableAliases[$tableKey] . '___' . $field->name,
            );
        }

        $query = $this->buildJoins($query, $this->entity, $i);

        return $query;
    }

    /** @param InspectedEntityInterface<T> $entity */
    private function buildJoins(SelectQuery $query, InspectedEntityInterface $entity, IncrementingInteger $i, string $rootProperty = 'root'): SelectQuery
    {
        foreach ($entity->joins() as $join) {
            if ($join->type !== 'inner') {
                continue;
            }

            if ($join->lazy === JoinInterface::IS_LAZY) {
                continue;
            }

            if ($entity->class() === $join->entity->class()) {
                continue;
            }

            $tableKey = spl_object_hash($join->entity) . '___' . $join->property;
            if (! array_key_exists($tableKey, $this->tableAliases)) {
                $this->tableAliases[$tableKey] = 't' . $i->getNext();
            }

            $clauses = null;
            foreach ($join->clause as $clause) {
                $onLeftSide = $this->tableAliases[$tableKey] . '.' . $clause->foreignKey;
                if ($clause->foreignFunction !== null) {
                    $onLeftSide = $clause->foreignFunction . '(' . $onLeftSide . ')';
                }

                if ($clause->foreignCast !== null) {
                    $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $clause->foreignCast . ')';
                }

                $onRightSide =
                    $this->tableAliases[spl_object_hash($entity) . '___' . $rootProperty] . '.' . $clause->localKey;
                if ($clause->localFunction !== null) {
                    $onRightSide = $clause->localFunction . '(' . $onRightSide . ')';
                }

                if ($clause->localCast !== null) {
                    $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $clause->localCast . ')';
                }

                if (! $clauses instanceof CriteriaInterface) {
                    $clauses = on($onLeftSide, $onRightSide);

                    continue;
                }

                $clauses = on($onLeftSide, $onRightSide)->and($clauses);
            }

            if ($clauses instanceof CriteriaInterface) {
                $query = $query->innerJoin(
                    alias(
                        $join->entity->table(),
                        $this->tableAliases[$tableKey],
                    ),
                    $clauses,
                );
            }

            foreach ($join->entity->fields() as $field) {
                $this->fields[$this->tableAliases[$tableKey] . '___' . $field->name] = alias($this->tableAliases[$tableKey] . '.' . $field->name, $this->tableAliases[$tableKey] . '___' . $field->name);
            }

            unset($this->fields[$entity->table() . '___' . $join->property]);

            /** @phpstan-ignore argument.type */
            $query = $this->buildJoins($query, $join->entity, $i, $join->property);
        }

        return $query;
    }

    /** @return iterable<T> */
    private function fetchAndHydrate(QueryInterface $query): iterable
    {
        foreach (
            $this->connection->query(
                $query->asExpression(),
            ) as $row
        ) {
            yield $this->hydrator->hydrate(
                $this->entity,
                $this->buildTree(
                    $this->inflate($row),
                    $this->entity,
                ),
            );
        }
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, array<string, mixed>>
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
     * @param array<string, array<string, mixed>> $row
     * @param InspectedEntityInterface<T>         $entity
     *
     * @return array<string, mixed>
     */
    private function buildTree(array $row, InspectedEntityInterface $entity, string $tableKeySuffix = 'root'): array
    {
        $tableKey = spl_object_hash($entity) . '___' . $tableKeySuffix;
        $tree     = $row[$this->tableAliases[$tableKey]];

        foreach ($entity->joins() as $join) {
            if ($join->type === 'inner' && $entity->class() !== $join->entity->class() && $join->lazy === false) {
                /** @phpstan-ignore argument.type */
                $tree[$join->property] = $this->buildTree($row, $join->entity, $join->property);

                continue;
            }

            if ($join->type === 'inner' && ($join->lazy === JoinInterface::IS_LAZY || $entity->class() === $join->entity->class())) {
                /** @phpstan-ignore argument.type */
                $tree[$join->property] = new ReflectionClass($join->entity->class())->newLazyProxy(function () use ($row, $join, $tableKey): EntityInterface|null {
//                    var_export([$row, $join, $tableKey]);
                    foreach ($join->clause as $clause) {
                        if ($row[$this->tableAliases[$tableKey]][$clause->localKey] === null) {
                            return null;
                        }
                    }

                    $where = [];

                    foreach ($join->clause as $clause) {
                        $onLeftSide = $clause->foreignKey;
                        if ($clause->foreignFunction !== null) {
                            /** @phpstan-ignore shipmonk.variableTypeOverwritten */
                            $onLeftSide = func($clause->foreignFunction, $onLeftSide);
                        }

                        if ($clause->foreignCast !== null) {
                            $onLeftSide = alias(func('CAST', $onLeftSide), $clause->foreignCast);
                        }

                        if (is_string($onLeftSide)) {
                            $where[] = new Where\Field(
                                $onLeftSide,
                                'eq',
                                [
                                    $row[$this->tableAliases[$tableKey]][$clause->localKey],
                                ],
                            );
                        } else {
                            $where[] = new Where\Expression(
                                $onLeftSide,
                                'eq',
                                [
                                    $row[$this->tableAliases[$tableKey]][$clause->localKey],
                                ],
                            );
                        }
                    }

                    foreach (
                            $this->client
                        ->repository(
                            $join->entity->class(),
                        )
                            ->fetch(
                                new Where(...$where),
                                new Limit(self::SINGLE),
                            ) as $entity
                    ) {
                        return $entity;
                    }

                    return null;
                });
                /** @phpstan-ignore method.deprecatedClass,new.deprecatedClass */
                $tree[$join->property] = new LazyPromise(fn (): PromiseInterface => new Promise(function (callable $resolve, callable $reject) use ($row, $join, $tableKey): void {
                    foreach ($join->clause as $clause) {
                        if ($row[$this->tableAliases[$tableKey]][$clause->localKey] === null) {
                            $resolve(null);

                            return;
                        }
                    }

                    $where = [];

                    foreach ($join->clause as $clause) {
                        $onLeftSide = $clause->foreignKey;
                        if ($clause->foreignFunction !== null) {
                            /** @phpstan-ignore shipmonk.variableTypeOverwritten */
                            $onLeftSide = func($clause->foreignFunction, $onLeftSide);
                        }

                        if ($clause->foreignCast !== null) {
                            $onLeftSide = alias(func('CAST', $onLeftSide), $clause->foreignCast);
                        }

                        if (is_string($onLeftSide)) {
                            $where[] = new Where\Field(
                                $onLeftSide,
                                'eq',
                                [
                                    $row[$this->tableAliases[$tableKey]][$clause->localKey],
                                ],
                            );
                        } else {
                            $where[] = new Where\Expression(
                                $onLeftSide,
                                'eq',
                                [
                                    $row[$this->tableAliases[$tableKey]][$clause->localKey],
                                ],
                            );
                        }
                    }

                    try {
                        $resolve([
                            ...$this->client
                            ->repository(
                                $join->entity->class(),
                            )
                            ->fetch(
                                new Where(...$where),
                                new Limit(self::SINGLE),
                            ),
                        ]);
                    } catch (Throwable $throwable) {
                        $reject($throwable);
                    }
                }));

                continue;
            }

            $tree[$join->property] = awaitObservable(Observable::defer(
                function () use ($row, $join, $tableKey): Observable {
                    $where = [];

                    foreach ($join->clause as $clause) {
                        $where[] = new Where\Field(
                            $clause->foreignKey,
                            'eq',
                            [
                                $row[$this->tableAliases[$tableKey]][$clause->localKey],
                            ],
                        );
                    }

                    return Observable::fromArray(
                        [...$this->client->repository($join->entity->class())->fetch(new Where(...$where))],
                        new ImmediateScheduler(),
                    );
                },
                new ImmediateScheduler(),
            ));
        }

        return $tree;
    }

    private function translateFieldName(string $name): string
    {
        $pos = strpos($name, '(');
        if ($pos === false) {
            return 't0.' . $name;
        }

        return substr($name, 0, $pos + 1) . 't0.' . substr($name, $pos + 1);
    }

    /**
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    private function prepareFields(array $fields): array
    {
        foreach ($fields as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                /** @phpstan-ignore shipmonk.variableTypeOverwritten */
                $fields[$key] = $value = date(
                    self::DATE_TIME_TIMEZ1_FORMAT,
                    (int) $value->format('U'),
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
