<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use Plasma\SQL\QueryExpressions\Fragment;
use Ramsey\Uuid\Uuid;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;
use WyriHaximus\React\SimpleORM\Annotation\JoinInterface;
use function Safe\substr;

final class Repository implements RepositoryInterface
{
    private const SINGLE = 1;

    /** @var InspectedEntityInterface */
    private $entity;

    /** @var ClientInterface */
    private $client;

    /** @var Hydrator */
    private $hydrator;

    /**
     * @var QueryBuilder
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $baseQuery;

    /** @var string[] */
    private $fields = [];

    /** @var string[] */
    private $tableAliases = [];

    public function __construct(InspectedEntityInterface $entity, ClientInterface $client)
    {
        $this->entity = $entity;
        $this->client = $client;
        $this->hydrator = new Hydrator();
    }

    public function count(): PromiseInterface
    {
        return $this->client->query(
            QueryBuilder::create()->from($this->entity->getTable())->select([
                'COUNT(*) AS count',
            ])
        )->take(self::SINGLE)->toPromise()->then(function (array $row): int {
            return (int)$row['count'];
        });
    }

    public function page(int $page, array $where = [], array $order = [], int $perPage = RepositoryInterface::DEFAULT_PER_PAGE): Observable
    {
        $query = $this->buildSelectQuery($where, $order);
        $query = $query->limit($perPage)->offset(--$page * $perPage);

        return $this->fetchAndHydrate($query);
    }

    public function fetch(array $where = [], array $order = [], int $limit = 0): Observable
    {
        $query = $this->buildSelectQuery($where, $order);
        if ($limit > 0) {
            $query = $query->limit($limit);
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
            QueryBuilder::create()->insert($fields)->into($this->entity->getTable())->returning()
        )->toPromise()->then(function (array $row) use ($id): PromiseInterface {
            return $this->fetch([
                [
                    'id',
                    '=',
                    $id,
                ],
            ])->take(1)->toPromise();
        });
    }

    public function update(EntityInterface $entity): PromiseInterface
    {
        $fields = $this->hydrator->extract($this->entity, $entity);
        $fields['modified'] = new \DateTimeImmutable();

        $fields = $this->prepareFields($fields);

        return $this->client->query(
            QueryBuilder::create()->
            update($fields)->
            into($this->entity->getTable())->
            where('id', '=', $entity->getId())
        )->toPromise()->then(function () use ($entity) {
            return $this->fetch([
                ['id', '=', $entity->getId()],
            ])->take(self::SINGLE)->toPromise();
        });
    }

    /**
     * @param mixed[] $where
     * @param mixed[] $order
     *
     * @return QueryBuilder
     */
    private function buildSelectQuery(array $where = [], array $order = []): QueryBuilder
    {
        $query = $this->getBaseQuery();

        $query = $query->select($this->fields);

        foreach ($where as $constraint) {
            $constraint[0] = $this->translateFieldName((string)$constraint[0]);
            $query = $query->where(...$constraint);
        }

        foreach ($order as $by) {
            $by[0] = $this->translateFieldName($by[0]);
            $query = $query->orderBy(...$by);
        }

        return $query;
    }

    private function getBaseQuery(): QueryBuilder
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if ($this->baseQuery === null) {
            $this->baseQuery = $this->buildBaseQuery();
        }

        return clone $this->baseQuery;
    }

    private function buildBaseQuery(): QueryBuilder
    {
        $i = 0;
        $tableKey = \spl_object_hash($this->entity) . '___root';
        $this->tableAliases[$tableKey] = 't' . $i++;
        $query = QueryBuilder::create()->from($this->entity->getTable(), $this->tableAliases[$tableKey]);

        foreach ($this->entity->getFields() as $field) {
            $this->fields[$this->tableAliases[$tableKey] . '___' . $field->getName()] = $this->tableAliases[$tableKey]. '.' . $field->getName();
        }

        $query = $this->buildJoins($query, $this->entity, $i);

        return $query;
    }

    private function buildJoins(QueryBuilder $query, InspectedEntityInterface $entity, int &$i, string $rootProperty = 'root'): QueryBuilder
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

            $query = $query->innerJoin(
                $join->getEntity()->getTable(),
                $this->tableAliases[$tableKey]
            );

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

                $query = $query->on($onLeftSide, $onRightSide);
            }

            foreach ($join->getEntity()->getFields() as $field) {
                $this->fields[$this->tableAliases[$tableKey] . '___' . $field->getName()] = $this->tableAliases[$tableKey] . '.' . $field->getName();
            }

            unset($this->fields[$entity->getTable() . '___' . $join->getProperty()]);

            $query = $this->buildJoins($query, $join->getEntity(), $i, $join->getProperty());
        }

        return $query;
    }

    private function fetchAndHydrate(QueryBuilder $query): Observable
    {
        return $this->client->query(
            $query
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
                $tree[$join->getProperty()] = new Promise(function (callable $resolve, callable $reject) use ($row, $join, $tableKey): void {
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
                            /** @psalm-suppress PossiblyNullOperand */
                            $onLeftSide = new Fragment($clause->getForeignFunction() . '(' . $onLeftSide . ')');
                        }
                        if ($clause->getForeignCast() !== null) {
                            /** @psalm-suppress PossiblyNullOperand */
                            $onLeftSide = new Fragment('CAST(' . (string)$onLeftSide . ' AS ' . $clause->getForeignCast() . ')');
                        }

                        $where[] = [
                            $onLeftSide,
                            '=',
                            $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                        ];
                    }

                    $this->client
                        ->getRepository($join->getEntity()
                            ->getClass())
                        ->fetch($where, [], self::SINGLE)
                        ->toPromise()
                        ->then($resolve, $reject);
                });

                continue;
            }

            $tree[$join->getProperty()] = Observable::defer(
                function () use ($row, $join, $tableKey) {
                    $where = [];

                    foreach ($join->getClause() as $clause) {
                        $where[] = [
                            $clause->getForeignKey(),
                            '=',
                            $row[$this->tableAliases[$tableKey]][$clause->getLocalKey()],
                        ];
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
                $fields[$key] = $value = $value->format('Y-m-d H:i:s');
            }

            if (\is_scalar($value)) {
                continue;
            }

            unset($fields[$key]);
        }

        return $fields;
    }
}
