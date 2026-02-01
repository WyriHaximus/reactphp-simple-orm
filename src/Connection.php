<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use React\Promise\PromiseInterface;

use function React\Promise\resolve;

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
        yield from $this->middlewareRunner->query(
            $query,
            fn (ExpressionInterface $query): PromiseInterface => resolve($this->adapter->query($query)),
        );
    }
}
