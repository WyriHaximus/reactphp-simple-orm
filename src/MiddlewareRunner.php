<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;

use function array_key_exists;

final class MiddlewareRunner
{
    /** @var array<MiddlewareInterface> */
    private array $middleware;

    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param callable(ExpressionInterface): iterable<array<string, mixed>> $last
     *
     * @return iterable<array<string, mixed>>
     */
    public function query(ExpressionInterface $query, callable $last): iterable
    {
        if (! array_key_exists(0, $this->middleware)) {
            return $last($query);
        }

        return $this->call($query, 0, $last);
    }

    /** @return iterable<array<string, mixed>> */
    private function call(ExpressionInterface $query, int $position, callable $last): iterable
    {
        $nextPosition = $position;
        $nextPosition++;
        // final request handler will be invoked without hooking into the promise
        if (! array_key_exists($nextPosition, $this->middleware)) {
            return $this->middleware[$position]->query($query, $last);
        }

        return $this->middleware[$position]->query($query, fn (ExpressionInterface $query): iterable => $this->call($query, $nextPosition, $last));
    }
}
