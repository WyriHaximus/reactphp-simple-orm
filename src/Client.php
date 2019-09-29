<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PgAsync\Client as PgClient;
use Plasma\SQL\Grammar\PostgreSQL;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\Middleware\ExecuteQueryMiddleware;
use WyriHaximus\React\SimpleORM\Middleware\GrammarMiddleware;
use function ApiClients\Tools\Rx\unwrapObservableFromPromise;
use function React\Promise\resolve;

final class Client implements ClientInterface
{
    /** @var AdapterInterface */
    private $adapter;

    /** @var EntityInspector */
    private $entityInspector;

    /** @var Repository[] */
    private $repositories = [];

    /** @var MiddlewareRunner */
    private $middlewareRunner;

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    public static function create(AdapterInterface $adapter, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, new AnnotationReader(), ...$middleware);
    }

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    public static function createWithAnnotationReader(AdapterInterface $adapter, Reader $annotationReader, MiddlewareInterface ...$middleware): self
    {
        return new self($adapter, $annotationReader, ...$middleware);
    }

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    private function __construct(AdapterInterface $adapter, Reader $annotationReader, MiddlewareInterface ...$middleware)
    {
        $this->adapter = $adapter;
        $this->entityInspector = new EntityInspector($annotationReader);

        $middleware[] = new GrammarMiddleware($adapter->getGrammar());

        $this->middlewareRunner = new MiddlewareRunner(...$middleware);
    }

    public function getRepository(string $entity): RepositoryInterface
    {
        if (!array_key_exists($entity, $this->repositories)) {
            $this->repositories[$entity] = new Repository($this->entityInspector->getEntity($entity), $this);
        }

        return $this->repositories[$entity];
    }

    public function query(QueryBuilder $query): Observable
    {
        return unwrapObservableFromPromise($this->middlewareRunner->query(
            $query,
            function (QueryBuilder $query): PromiseInterface
            {
                return resolve($this->adapter->query($query));
            }
        ));
    }
}
