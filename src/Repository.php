<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

final class Repository implements RepositoryInterface
{
    /** @var InspectedEntity */
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

    public function __construct(InspectedEntity $entity, ClientInterface $client)
    {
        $this->entity = $entity;
        $this->client = $client;
        $this->hydrator = new Hydrator();
    }

    public function count(): PromiseInterface
    {
        return $this->client->fetch(
            QueryBuilder::create()->from($this->entity->getTable())->select([
                'COUNT(*) AS count',
            ])
        )->take(1)->toPromise()->then(function (array $row): int {
            return (int)$row['count'];
        });
    }

    public function page(int $page, array $where = [], array $order = [], int $perPage = RepositoryInterface::DEFAULT_PER_PAGE): Observable
    {
        $query = $this->buildSelectQuery($where, $order);
        $query = $query->limit($perPage)->offset(--$page * $perPage);

        return $this->fetchAndHydrate(
            $query
        );
    }

    public function fetch(array $where = [], array $order = []): Observable
    {
        return $this->fetchAndHydrate(
            $this->buildSelectQuery($where, $order)
        );
    }

    private function buildSelectQuery(array $where = [], array $order = []): QueryBuilder
    {
        $query = $this->getBaseQuery();

        $query = $query->select($this->fields);

        foreach ($where as $constraint) {
            $constraint[0] = $this->translateFieldName($constraint[0]);
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

    private function buildJoins(QueryBuilder $query, InspectedEntity $entity, int &$i): QueryBuilder
    {
        foreach ($entity->getJoins() as $join) {
            if ($join->getType() !== 'inner') {
                continue;
            }

            $tableKey = \spl_object_hash($join->getEntity()) . '___' . $join->getProperty();
            $this->tableAliases[$tableKey] = 't' . $i++;

            $foreignTable = $join->getEntity()->getTable();
            $onLeftSide = $this->tableAliases[$tableKey] . '.' . $join->getForeignKey();
            if ($join->getForeignCast() !== null) {
                /** @psalm-suppress PossiblyNullOperand */
                $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $join->getForeignCast() . ')';
            }
            $onRightSide =
                $this->tableAliases[\spl_object_hash($entity) . '___root'] . '.' . $join->getLocalKey();
            if ($join->getLocalCast() !== null) {
                /** @psalm-suppress PossiblyNullOperand */
                $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $join->getLocalCast() . ')';
            }

            $query = $query->innerJoin(
                $foreignTable,
                $this->tableAliases[$tableKey]
            )->on(
                $onLeftSide,
                $onRightSide
            );

            foreach ($join->getEntity()->getFields() as $field) {
                $this->fields[$this->tableAliases[$tableKey] . '___' . $field->getName()] = $this->tableAliases[$tableKey] . '.' . $field->getName();
            }

            unset($this->fields[$entity->getTable() . '___' . $join->getProperty()]);

            $query = $this->buildJoins($query, $join->getEntity(), $i);
        }

        return $query;
    }

    private function fetchAndHydrate(QueryBuilder $query): Observable
    {
        return $this->client->fetch(
            $query
        )->map(function (array $row): array {
            return $this->inflate($row);
        })->map(function (array $row): array {
            return $this->buildTree($row, $this->entity);
        })->map(function (array $row): EntityInterface {
            return $this->hydrator->hydrate($this->entity, $row);
        });
    }

    private function inflate(array $row): array
    {
        $tables = [];

        foreach ($row as $key => $value) {
            [$table, $field] = \explode('___', $key);
            $tables[$table][$field] = $value;
        }

        return $tables;
    }

    private function buildTree(array $row, InspectedEntity $entity, string $tableKeySuffix = 'root'): array
    {
        $tableKey = \spl_object_hash($entity) . '___' . $tableKeySuffix;
        $tree = $row[$this->tableAliases[$tableKey]];

        foreach ($entity->getJoins() as $join) {
            if ($join->getType() === 'inner') {
                $tree[$join->getProperty()] = $this->buildTree($row, $join->getEntity(), $join->getProperty());

                continue;
            }

            $tree[$join->getProperty()] = Observable::defer(
                function () use ($row, $join, $tableKey) {
                    $where = [];

                    $where[] = [
                        $join->getForeignKey(),
                        '=',
                        $row[$this->tableAliases[$tableKey]][$join->getLocalKey()],
                    ];

                    return $this->client->getRepository($join->getEntity()->getClass())->fetch($where);
                },
                new ImmediateScheduler()
            );
        }

        return $tree;
    }

    private function translateFieldName(string $name): string
    {
        return 't0.' . $name;
    }
}
