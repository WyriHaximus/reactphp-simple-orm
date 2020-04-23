<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryInterface;
use React\Promise\PromiseInterface;

interface MiddlewareInterface
{
    /**
     * Returns the (modified) query through a promise.
     *
     * @param ExpressionInterface $query
     * @param callable $next
     *
     * @return PromiseInterface
     */
    public function query(ExpressionInterface $query, callable $next): PromiseInterface;
}
