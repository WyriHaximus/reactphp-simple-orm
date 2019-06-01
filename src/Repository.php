<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use ReflectionClass;
use ReflectionProperty;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\RightJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;

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
            })($this->getBaseQuery()->select($this->entity->getFields()), $where, $page, $perPage)
        )->map(function (array $row): array {
            return $this->inflate($row);
        })->map(function (array $row): object {
            return $this->hydrator->hydrate($this->entity->getClass(), $row);
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
            })($this->getBaseQuery()->select($this->entity->getFields()), $where)
        )->map(function (array $row): array {
            return $this->inflate($row);
        })->map(function (array $row): object {
            return $this->hydrator->hydrate($this->entity->getClass(), $row);
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
        $query = QueryBuilder::create()->from($this->table, $this->table);

        /** @var ReflectionProperty $property */
        foreach ((new ReflectionClass($this->entity))->getProperties() as $property) {
            $this->fields[$this->table . '___' . $property->getName()] = $this->table . '.' . $property->getName();
        }

        $annotations = $this->annotationReader->getClassAnnotations(new ReflectionClass($this->entity));

        /** @var InnerJoin|null $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation instanceof InnerJoin === false && $annotation instanceof LeftJoin === false  && $annotation instanceof RightJoin === false) {
                continue;
            }

            $joinMethod = 'innerJoin';
            if ($annotation instanceof LeftJoin) {
                $joinMethod = 'leftJoin';
            }
            if ($annotation instanceof RightJoin) {
                $joinMethod = 'rightJoin';
            }

            $foreignTable = $this->annotationReader->getClassAnnotation(new ReflectionClass($annotation->getEntity()), Table::class)->getTable();
            $onLeftSide = $foreignTable . '.' . $annotation->getForeignKey();
            if ($annotation->getForeignCast() !== null) {
                $onLeftSide = 'CAST(' . $onLeftSide . ' AS ' . $annotation->getForeignCast() . ')';
            }
            $onRightSide = $this->table . '.' . $annotation->getLocalKey();
            if ($annotation->getLocalCast() !== null) {
                $onRightSide = 'CAST(' . $onRightSide . ' AS ' . $annotation->getLocalCast() . ')';
            }
            $query = $query->$joinMethod(
                $foreignTable,
                $foreignTable
            )->on(
                $onLeftSide,
                $onRightSide
            );

            /** @var ReflectionProperty $property */
            foreach ((new ReflectionClass($annotation->getEntity()))->getProperties() as $property) {
                $this->fields[$foreignTable . '___' . $property->getName()] = $foreignTable . '.' . $property->getName();
            }

            if ($annotation->getProperty() !== null) {
                unset($this->fields[$this->table . '___' . $annotation->getProperty()]);
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
