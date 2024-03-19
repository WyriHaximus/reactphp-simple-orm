<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use React\Promise\PromiseInterface;
use Rx\Observable;

use function React\Promise\resolve;

/** @internal */
final readonly class Connection
{
    public function __construct(
        private AdapterInterface $adapter,
        private MiddlewareRunner $middlewareRunner,
    ) {
    }

    public function query(ExpressionInterface $query): Observable
    {
        return Observable::fromPromise($this->middlewareRunner->query(
            $query,
            function (ExpressionInterface $query): PromiseInterface {
                return resolve($this->adapter->query($query));
            },
        ))->mergeAll();
    }
}
