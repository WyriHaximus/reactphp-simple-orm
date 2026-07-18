<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;

final readonly class Connection
{
    public function __construct(
        private AdapterInterface $adapter,
        private MiddlewareRunner $middlewareRunner,
    ) {
    }

    /** @return iterable<array<string, mixed>> */
    public function query(ExpressionInterface $query): iterable
    {
        return $this->middlewareRunner->query(
            $query,
            fn (ExpressionInterface $query): iterable => $this->adapter->query($query),
        );
    }
}
