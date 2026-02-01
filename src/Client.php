<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryFactory;

use function array_key_exists;

final class Client implements ClientInterface
{
    private readonly EntityInspector $entityInspector;

    /** @var array<RepositoryInterface> */
    private array $repositories = [];

    private readonly Connection $connection;

    private readonly QueryFactory $queryFactory;

    private readonly Hydrator $hydrator;

    public static function create(AdapterInterface $adapter, Configuration $configuration, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $configuration, ...$middleware);
    }

    private function __construct(private readonly AdapterInterface $adapter, Configuration $configuration, MiddlewareInterface ...$middleware)
    {
        $this->entityInspector = new EntityInspector($configuration);
        $this->queryFactory    = new QueryFactory($adapter->engine());

        $this->connection = new Connection($this->adapter, new MiddlewareRunner(...$middleware));
        $this->hydrator   = new Hydrator();
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
            $this->repositories[$entity] = new Repository(
                $this->entityInspector->entity($entity),
                $this,
                $this->queryFactory,
                $this->connection,
                $this->hydrator,
            );
        }

        return $this->repositories[$entity];
    }

    /** @return iterable<array<string, mixed>> */
    public function query(ExpressionInterface $query): iterable
    {
        return $this->connection->query($query);
    }
}
