<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Middleware;

use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\MiddlewareInterface;
use function React\Promise\resolve;

final class ExecuteQueryMiddleware implements MiddlewareInterface
{
    /** @var PgClient */
    private $client;

    public function __construct(PgClient $client)
    {
        $this->client = $client;
    }

    public function query(QueryBuilder $query): PromiseInterface
    {
        return resolve($this->client->executeStatement($query->getQuery(), $query->getParameters()));
    }
}
