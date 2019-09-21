<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

/**
 * @internal
 */

final class MiddlewareRunner
{
    /** @var MiddlewareInterface[] */
    private $middleware;

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->middleware = $middleware;
    }

    public function query(QueryBuilder $query, callable $last): PromiseInterface
    {
        return $this->call($query, 0, $last);
    }

    private function call(QueryBuilder $query, int $position, callable $last): PromiseInterface
    {
        // final request handler will be invoked without hooking into the promise
        if (!array_key_exists($position + 1, $this->middleware)) {
            return $this->middleware[$position]->query($query, $last);
        }

        return $this->middleware[$position]->query($query, function (QueryBuilder $query) use ($position, $last) {
            return $this->call($query, $position + 1, $last);
        });
    }
}
