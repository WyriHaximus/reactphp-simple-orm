<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

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
use WyriHaximus\React\SimpleORM\Annotation\JoinInterface;
use WyriHaximus\React\SimpleORM\Query\ExpressionWhere;
use WyriHaximus\React\SimpleORM\Query\Where;
use function Latitude\QueryBuilder\alias;
use function Latitude\QueryBuilder\field;
use function Latitude\QueryBuilder\func;
use function Latitude\QueryBuilder\on;
use function Safe\substr;
use const WyriHaximus\Constants\Numeric\ONE;
use const WyriHaximus\Constants\Numeric\ZERO;

final class Repository implements RepositoryInterface
{
    private const SINGLE = 1;

    /** @var InspectedEntityInterface */
    private $entity;

    /** @var ClientInterface */
    private $client;

    /** @var QueryFactory */
    private $queryFactory;

    /** @var Hydrator */
    private $hydrator;

    /**
     * @var QueryInterface
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $baseQuery;

    /** @var ExpressionInterface[] */
    private $fields = [];

    /** @var string[] */
    private $tableAliases = [];

    public function __construct(InspectedEntityInterface $entity, ClientInterface $client, QueryFactory $queryFactory)
    {
        $this->entity = $entity;
        $this->client = $client;
        $this->queryFactory = $queryFactory;
        $this->hydrator = new Hydrator();
    }

    public function count(): PromiseInterface
    {
        return $this->client->query(
            $this->queryFactory->select(alias(func('COUNT', '*'), 'count'))->from($this->entity->getTable())->asExpression()
        )->take(self::SINGLE)->toPromise()->then(function (array $row): int {
            return (int)$row['count'];
        });
    }

    /**
     * @param int $page
     * @param Where[]|ExpressionWhere[] $where
     * @param array $order
     * @param int $perPage
     * @return Observable
     */
    public function page(int $page, array $where = [], array $order = [], int $perPage = RepositoryInterface::DEFAULT_PER_PAGE): Observable
    {
        $query = $this->buildSelectQuery($where, $order);
        $query = $query->limit($perPage)->offset(--$page * $perPage);

        return $this->fetchAndHydrate($query);
    }

    /**
     * @param Where[]|ExpressionWhere[] $where
     * @param array $order
     * @param int $limit
     *
     * @return Observable
     */
    public function fetch(array $where = [], array $order = [], int $limit = 0): Observable
    {
        $query = $this->buildSelectQuery($where, $order);
        if ($limit > 0) {
            $query = $query->limit($limit)->offset(0);
        }

        return $this->fetchAndHydrate($query);
    }

    public function create(array $fields): PromiseInterface
    {
        $id = Uuid::getFactory()->uuid4()->toString();
        $fields['id'] = $id;
        $fields['created'] = new \DateTimeImmutable();
        $fields['modified'] = new \DateTimeImmutable();

        $fields = $this->prepareFields($fields);

        return $this->client->query(
            $this->queryFactory->insert($this->entity->getTable(), $fields)->asExpression()
        )->toPromise()->then(function () use ($id): PromiseInterface {
            return $this->fetch([
                new Where(
                    'id',
                    'eq',
                    [$id],
                ),
            ])->take(1)->toPromise();
        });
    }

    public function update(EntityInterface $entity): PromiseInterface
    {
        $fields = $this->hydrator->extract($this->entity, $entity);
        $fields['modified'] = new \DateTimeImmutable();
        $fields = $this->prepareFields($fields);

        return $this->client->query(
            $this->queryFactory->update($this->entity->getTable(), $fields)->
            where(field('id')->eq($entity->getId()))->asExpression()
        )->toPromise()->then(function () use ($entity) {
            return $this->fetch([
                new Where('id', 'eq', [$entity->getId()]),
            ], [], ONE)->toPromise();
        });
    }

    /**
     * @param Where[]|ExpressionWhere[] $constraints
     * @param mixed[] $order
     *
     * @return SelectQuery
     */
    private function buildSelectQuery(array $constraints, array $order): SelectQuery
    {
        $query = $this->buildBaseSelectQuery();

        $query = $query->columns(...array_values($this->fields));

        $whereCount = count($constraints);
        if ($whereCount > 0) {
            foreach ($constraints as $i => $constraint) {
                if ($constraint instanceof ExpressionWhere) {
                    $where = $constraint->expression();
                    $where = $constraint->applyExpression($where);
                } else {
                    $where = field($this->translateFieldName($constraint->field()));
                    $where = $constraint->applyCriteria($where);
                }

                if ($i === 0) {
                    $query = $query->where($where);
                    continue;
                }

                $query = $query->andWhere($where);
            }
        }

        foreach ($order as $by) {
            $by[0] = $this->translateFieldName($by[ZERO]);
            $query = $query->orderBy($by[ZERO], $by[ONE] ? 'desc' : 'asc');
        }

        return $query;
    }

    private function buildBaseSelectQuery(): SelectQuery
    {
        $i = 0;
        $tableKey = \spl_object_hash($this->entity) . '___root';
        $this->tableAliases[$tableKey] = 't' . $i++;
        $query = $this->queryFactory->select()->from(alias($this->entity->getTable(), $this->tableAliases[$tableKey]));

        foreach ($this->entity->getFields() as $field) {
            $this->fields[$this->tableAliases[$tableKey] . '___' . $field->getName()] = alias($this->tableAliases[$tableKey]. '.' . $field->getName(), $this->tableAliases[$tableKey] . '___' . $field->getName());
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

            if ($join->getType() === 'inner' && $entity->getClass() === $join->getEntity()->getClass()) {
                continue;
            }

            $tableKey = \spl_object_hash($join->getEntity()) . '___' . $join->getProperty();
            if (!array_key_exists($tableKey, $this->tableAliases)) {
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
                    $this->tableAliases[\spl_object_hash($entity) . '___' . $rootProperty] . '.' . $clause->getLocalKey();
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

            /** @psalm-suppress PossiblyNullArgument */
            $query = $query->innerJoin(
                alias(
                    $join->getEntity()->getTable(), $this->tableAliases[$tableKey]
                ),
                $clauses
            );

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
            [$table, $field] = \explode('___', $key);
            $tables[$table][$field] = $value;
        }

        return $tables;
    }

    /**
     * @param mixed[] $row
     * @param InspectedEntityInterface $entity
     * @param string $tableKeySuffix
     *
     * @return mixed[]
     */
    private function buildTree(array $row, InspectedEntityInterface $entity, string $tableKeySuffix = 'root'): array
    {
        $tableKey = \spl_object_hash($entity) . '___' . $tableKeySuffix;
        $tree = $row[$this->tableAliases[$tableKey]];

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

                            $whereClass = ExpressionWhere::class;
                            if (is_string($onLeftSide)) {
                                $whereClass = Where::class;
                            }
                            $where[] = new $whereClass(
                                $onLeftSide,
                                'eq',
                                [
                                    $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                                ]
                            );
                        }

                        $this->client
                            ->getRepository($join->getEntity()
                            ->getClass())
                            ->fetch($where, [], self::SINGLE)
                            ->toPromise()
                            ->then($resolve, $reject);
                        });
                });

                continue;
            }

            $tree[$join->getProperty()] = Observable::defer(
                function () use ($row, $join, $tableKey) {
                    $where = [];

                    foreach ($join->getClause() as $clause) {
                        $where[] = new Where(
                            $clause->getForeignKey(),
                            'eq',
                            [
                                $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                            ]
                        );
                    }

                    return $this->client->getRepository($join->getEntity()->getClass())->fetch($where);
                },
                new ImmediateScheduler()
            );
        }

        return $tree;
    }

    private function translateFieldName(string $name): string
    {
        $pos = \strpos($name, '(');
        if ($pos === false) {
            return 't0.' . $name;
        }

        return substr($name, 0, $pos + 1) . 't0.' . substr($name, $pos + 1);
    }

    /**
     * @param mixed[] $fields
     *
     * @return mixed[]
     */
    private function prepareFields(array $fields): array
    {
        foreach ($fields as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $fields[$key] = $value = date('Y-m-d H:i:s e', (int) $value->format('U'));
            }

            if (\is_scalar($value)) {
                continue;
            }

            unset($fields[$key]);
        }

        return $fields;
    }
}
