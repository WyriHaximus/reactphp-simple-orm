<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryFactory;
use React\Promise\PromiseInterface;
use Rx\Observable;

use function ApiClients\Tools\Rx\unwrapObservableFromPromise;
use function array_key_exists;
use function React\Promise\resolve;

final class Client implements ClientInterface
{
    private AdapterInterface $adapter;

    private EntityInspector $entityInspector;

    /** @var array<string, Repository> */
    private array $repositories = [];

    private MiddlewareRunner $middlewareRunner;

    private QueryFactory $queryFactory;

    public static function create(AdapterInterface $adapter, Configuration $configuration, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $configuration, new AnnotationReader(), ...$middleware);
    }

    public static function createWithAnnotationReader(AdapterInterface $adapter, Configuration $configuration, Reader $annotationReader, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $configuration, $annotationReader, ...$middleware);
    }

    private function __construct(AdapterInterface $adapter, Configuration $configuration, Reader $annotationReader, MiddlewareInterface ...$middleware)
    {
        $this->adapter         = $adapter;
        $this->entityInspector = new EntityInspector($configuration, $annotationReader);
        $this->queryFactory    = new QueryFactory($adapter->engine());

        $this->middlewareRunner = new MiddlewareRunner(...$middleware);
    }

    public function repository(string $entity): RepositoryInterface
    {
        if (! array_key_exists($entity, $this->repositories)) {
            $this->repositories[$entity] = new Repository($this->entityInspector->entity($entity), $this, $this->queryFactory);
        }

        return $this->repositories[$entity];
    }

    public function query(ExpressionInterface $query): Observable
    {
        return unwrapObservableFromPromise($this->middlewareRunner->query(
            $query,
            function (ExpressionInterface $query): PromiseInterface {
                return resolve($this->adapter->query($query));
            }
        ));
    }
}
