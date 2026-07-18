<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;

interface MiddlewareInterface
{
    /**
     * @param callable(ExpressionInterface): iterable<array<string, mixed>> $next
     *
     * @return iterable<array<string, mixed>>
     */
    public function query(ExpressionInterface $query, callable $next): iterable;
}
