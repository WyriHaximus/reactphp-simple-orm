<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\RightJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;

final class Repository
{
    /** @var ClientInterface */
    private $client;

    /** @var Hydrator */
    private $hydrator;

    /** @var QueryBuilder */
    private $baseQuery;

    /** @var string */
    private $table;

    /** @var Reader */
    private $annotationReader;

    /**
     * @var string
     */
    private $entity;

    public function __construct(ClientInterface $client, string $entity)
    {
        $this->client = $client;
        $this->entity = $entity;
        $this->hydrator = new Hydrator();
        $this->annotationReader = new AnnotationReader();
        $this->table = $this->annotationReader->getClassAnnotation(new \ReflectionClass($entity), Table::class)->getTable();
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

    private function getBaseQuery(): QueryBuilder
    {
        if ($this->baseQuery === null) {
            $this->baseQuery = $this->buildBaseQuery();
        }

        return $this->baseQuery;
    }

    private function buildBaseQuery(): QueryBuilder
    {
        $query = QueryBuilder::create()->from($this->table, $this->table);

        $annotations = $this->annotationReader->getClassAnnotations(new \ReflectionClass($this->entity));

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

            $foreignTable = $this->annotationReader->getClassAnnotation(new \ReflectionClass($annotation->getEntity()), Table::class)->getTable();
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
        }

        return $query;
    }
}
