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
    /** @var PgClient */
    private $client;

    /** @var EntityInspector */
    private $entityInspector;

    /** @var Repository[] */
    private $repositories = [];

    /** @var MiddlewareRunner */
    private $middlewareRunner;

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    public static function create(PgClient $client, MiddlewareInterface ...$middleware): self
    {
        return new self($client, new AnnotationReader(), ...$middleware);
    }

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    public static function createWithAnnotationReader(PgClient $client, Reader $annotationReader, MiddlewareInterface ...$middleware): self
    {
        return new self($client, $annotationReader, ...$middleware);
    }

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    private function __construct(PgClient $client, Reader $annotationReader, MiddlewareInterface ...$middleware)
    {
        $this->client = $client;
        $this->entityInspector = new EntityInspector($annotationReader);

        $middleware[] = new GrammarMiddleware(new PostgreSQL());

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
                return resolve($this->client->executeStatement($query->getQuery(), $query->getParameters()));
            }
        ));
    }
}
