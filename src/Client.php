<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryFactory;
use React\Promise\PromiseInterface;
use Rx\Observable;

use function array_key_exists;
use function React\Promise\resolve;

final class Client implements ClientInterface
{
    private EntityInspector $entityInspector;

    private array $repositories = [];

    private Connection $connection;

    private QueryFactory $queryFactory;

    public static function create(AdapterInterface $adapter, Configuration $configuration, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $configuration, new AnnotationReader(), ...$middleware);
    }

    public static function createWithAnnotationReader(AdapterInterface $adapter, Configuration $configuration, Reader $annotationReader, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $configuration, $annotationReader, ...$middleware);
    }

    private function __construct(private AdapterInterface $adapter, Configuration $configuration, Reader $annotationReader, MiddlewareInterface ...$middleware)
    {
        $this->entityInspector = new EntityInspector($configuration, $annotationReader);
        $this->queryFactory    = new QueryFactory($adapter->engine());

        $this->connection = new Connection($this->adapter, new MiddlewareRunner(...$middleware));
    }

    /**
     * @template T
     * @param class-string<T> $entity
     * @return RepositoryInterface<T>
     */
    public function repository(string $entity): RepositoryInterface
    {
        if (! array_key_exists($entity, $this->repositories)) {
            $this->repositories[$entity] = new Repository($this->entityInspector->entity($entity), $this, $this->queryFactory, $this->connection);
        }

        return $this->repositories[$entity];
    }

    public function query(ExpressionInterface $query): Observable
    {
        return $this->connection->query($query);
    }
}
