<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use React\Promise\PromiseInterface;

interface MiddlewareInterface
{
    /**
     * Returns the (modified) query through a promise.
     */
    public function query(ExpressionInterface $query, callable $next): PromiseInterface;
}
