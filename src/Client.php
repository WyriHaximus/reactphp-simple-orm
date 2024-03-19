<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryFactory;
use Rx\Observable;

use function array_key_exists;

final class Client implements ClientInterface
{
    private EntityInspector $entityInspector;

    private array $repositories = [];

    private Connection $connection;

    private QueryFactory $queryFactory;

    public static function create(AdapterInterface $adapter, Configuration $configuration, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $configuration, ...$middleware);
    }

    private function __construct(private AdapterInterface $adapter, Configuration $configuration, MiddlewareInterface ...$middleware)
    {
        $this->entityInspector = new EntityInspector($configuration);
        $this->queryFactory    = new QueryFactory($adapter->engine());

        $this->connection = new Connection($this->adapter, new MiddlewareRunner(...$middleware));
    }

    /**
     * @param class-string<T> $entity
     *
     * @return RepositoryInterface<T>
     *
     * @template T
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
