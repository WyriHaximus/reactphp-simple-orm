<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Middleware;

use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\MiddlewareInterface;
use function React\Promise\resolve;

final class QueryCountMiddleware implements MiddlewareInterface
{
    private const ZERO = 0;

    /** @var int */
    private $count = self::ZERO;

    public function query(QueryBuilder $query): PromiseInterface
    {
        $this->count++;

        return resolve($query);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function resetCount(): void
    {
        $this->count = 0;
    }
}
