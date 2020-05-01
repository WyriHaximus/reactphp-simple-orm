<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use React\Promise\PromiseInterface;
use function array_key_exists;
use const WyriHaximus\Constants\Numeric\ZERO;

/**
 * @internal
 */

final class MiddlewareRunner
{
    /** @var MiddlewareInterface[] */
    private array $middleware;

    /**
     * @param array<int, MiddlewareInterface> $middleware
     */
    public function __construct(MiddlewareInterface ...$middleware)
    {
        $this->middleware = $middleware;
    }

    public function query(ExpressionInterface $query, callable $last): PromiseInterface
    {
        if (! array_key_exists(ZERO, $this->middleware)) {
            return $last($query);
        }

        return $this->call($query, ZERO, $last);
    }

    private function call(ExpressionInterface $query, int $position, callable $last): PromiseInterface
    {
        $nextPosition = $position;
        $nextPosition++;
        // final request handler will be invoked without hooking into the promise
        if (! array_key_exists($nextPosition, $this->middleware)) {
            return $this->middleware[$position]->query($query, $last);
        }

        return $this->middleware[$position]->query($query, function (ExpressionInterface $query) use ($nextPosition, $last): PromiseInterface {
            return $this->call($query, $nextPosition, $last);
        });
    }
}
