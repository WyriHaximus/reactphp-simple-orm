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

    public function page(int $page, array $where = [], array $order = [], int $perPage = 50): Observable
    {
        return $this->client->fetch(
            (function (QueryBuilder $query, array $where, int $page, int $perPage): QueryBuilder {
                foreach ($where as $constraint) {
                    $query = $query->where(...$constraint);
                }

                $query = $query->limit($perPage)->offset(--$page * $perPage)->orderBy('screenshots___id', true);

                return $query;
            })($this->getBaseQuery()->select($this->fields), $where, $page, $perPage)
        )->map(function (array $row): array {
            return $this->inflate($row);
        })->map(function (array $row): array {
            return $this->buildTree($row, $this->entity);
        })->map(function (array $row): EntityInterface {
            return $this->hydrator->hydrate($this->entity, $row);
        });
    }

    public function fetch(array $where = []): Observable
    {
        return $this->client->fetch(
            (function (QueryBuilder $query, array $where): QueryBuilder {
                foreach ($where as $constraint) {
                    $query = $query->where(...$constraint);
                }

                return $query;
            })($this->getBaseQuery()->select($this->fields), $where)
        )->map(function (array $row): array {
            return $this->inflate($row);
        })->map(function (array $row): array {
            return $this->buildTree($row, $this->entity);
        })->map(function (array $row): EntityInterface {
            return $this->hydrator->hydrate($this->entity, $row);
        });
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
        $this->tableAliases[\spl_object_hash($this->entity)] = 't' . $i++;
        $query = QueryBuilder::create()->from($this->entity->getTable(), $this->tableAliases[\spl_object_hash($this->entity)]);

        foreach ($this->entity->getFields() as $field) {
            $this->fields[$this->tableAliases[\spl_object_hash($this->entity)] . '___' . $field->getName()] = $this->tableAliases[\spl_object_hash($this->entity)]. '.' . $field->getName();
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

            $this->tableAliases[\spl_object_hash($join->getEntity())] = 't' . $i++;
//            $joinMethod = 'innerJoin';
//            if ($join->getType() === 'left') {
//                $joinMethod = 'leftJoin';
//            }
//            if ($join->getType() === 'right') {
//                $joinMethod = 'rightJoin';
//            }

            $foreignTable = $join->getEntity()->getTable();
            $onLeftSide = $this->tableAliases[\spl_object_hash($join->getEntity())] . '.' . $join->getForeignKey();
            if ($join->getForeignCast() !== null) {
                /** @psalm-suppress PossiblyNullOperand */
                $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $join->getForeignCast() . ')';
            }
            $onRightSide =
                $this->tableAliases[\spl_object_hash($entity)] . '.' . $join->getLocalKey();
            if ($join->getLocalCast() !== null) {
                /** @psalm-suppress PossiblyNullOperand */
                $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $join->getLocalCast() . ')';
            }
//            $query = $query->$joinMethod(
            $query = $query->innerJoin(
                $foreignTable,
                $this->tableAliases[\spl_object_hash($join->getEntity())]
            )->on(
                $onLeftSide,
                $onRightSide
            );

            foreach ($join->getEntity()->getFields() as $field) {
                $this->fields[$this->tableAliases[\spl_object_hash($join->getEntity())] . '___' . $field->getName()] = $this->tableAliases[\spl_object_hash($join->getEntity())] . '.' . $field->getName();
            }

            unset($this->fields[$entity->getTable() . '___' . $join->getProperty()]);

            $query = $this->buildJoins($query, $join->getEntity(), $i);
        }

        return $query;
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

    private function buildTree(array $row, InspectedEntity $entity): array
    {
        $tree = $row[$this->tableAliases[\spl_object_hash($entity)]];

        foreach ($entity->getJoins() as $join) {
            if ($join->getType() === 'inner') {
                $tree[$join->getProperty()] = $this->buildTree($row, $join->getEntity());

                continue;
            }

            $tree[$join->getProperty()] = Observable::defer(
                function () use ($row, $entity, $join) {
                    $where = [];

                    $where[] = [
                        $join->getForeignKey(),
                        '=',
                        $row[$this->tableAliases[\spl_object_hash($entity)]][$join->getLocalKey()],
                    ];

                    return $this->client->getRepository($join->getEntity()->getClass())->fetch($where);
                },
                new ImmediateScheduler()
            );
        }

        return $tree;
    }
}
