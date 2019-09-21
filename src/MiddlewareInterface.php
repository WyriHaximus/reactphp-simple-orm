<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;

interface MiddlewareInterface
{
    /**
     * Returns the (modified) query through a promise.
     *
     * @param QueryBuilder $query
     * @param callable $next
     *
     * @return PromiseInterface
     */
    public function query(QueryBuilder $query, callable $next): PromiseInterface;
}
