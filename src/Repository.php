<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use ReflectionClass;
use ReflectionProperty;
use Rx\Observable;

final class Repository
{
    /** @var InspectedEntity */
    private $entity;

    /** @var ClientInterface */
    private $client;

    /** @var Hydrator */
    private $hydrator;

    /** @var QueryBuilder */
    private $baseQuery;

    /** @var string[] */
    private $fields = [];

    public function __construct(InspectedEntity $entity, ClientInterface $client)
    {
        $this->entity = $entity;
        $this->client = $client;
        $this->hydrator = new Hydrator();
    }

    public function count(): PromiseInterface
    {
        return $this->client->fetch(
            $this->getBaseQuery()->select([
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
        })->map(function (array $row): object {
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
        })->map(function (array $row): object {
            return $this->hydrator->hydrate($this->entity, $row);
        });
    }

    private function getBaseQuery(): QueryBuilder
    {
        if ($this->baseQuery === null) {
            $this->baseQuery = $this->buildBaseQuery();
        }

        return clone $this->baseQuery;
    }

    private function buildBaseQuery(): QueryBuilder
    {
        $query = QueryBuilder::create()->from($this->entity->getTable(), $this->entity->getTable());

        foreach ($this->entity->getFields() as $field) {
            $this->fields[$this->entity->getTable() . '___' . $field->getName()] = $this->entity->getTable() . '.' . $field->getName();
        }

        foreach ($this->entity->getJoins() as $join) {
            $joinMethod = 'innerJoin';
            if ($join->getType() === 'left') {
                $joinMethod = 'leftJoin';
            }
            if ($join->getType() === 'right') {
                $joinMethod = 'rightJoin';
            }

            $foreignTable = $join->getEntity()->getTable();
            $onLeftSide = $foreignTable . '.' . $join->getForeignKey();
            if ($join->getForeignCast() !== null) {
                $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $join->getForeignCast() . ')';
            }
            $onRightSide = $this->entity->getTable() . '.' . $join->getLocalKey();
            if ($join->getLocalCast() !== null) {
                $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $join->getLocalCast() . ')';
            }
            $query = $query->$joinMethod(
                $foreignTable,
                $foreignTable
            )->on(
                $onLeftSide,
                $onRightSide
            );

            foreach ($join->getEntity()->getFields() as $field) {
                $this->fields[$foreignTable . '___' . $field->getName()] = $foreignTable . '.' . $field->getName();
            }

            if ($join->getProperty() !== null) {
                unset($this->fields[$this->entity->getTable() . '___' . $join->getProperty()]);
            }
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
}
