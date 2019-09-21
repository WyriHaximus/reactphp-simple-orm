<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Plasma\SQL\QueryBuilder;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\ExecuteQueryMiddleware;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use function ApiClients\Tools\Rx\observableFromArray;
use function Safe\sleep;
use function WyriHaximus\iteratorOrArrayToArray;

/**
 * @internal
 */
final class QueryCountMiddlewareTest extends AsyncTestCase
{
    public function testCountingSuccess(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred = new Deferred();

        $middleware->query(QueryBuilder::create(), function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred->resolve(observableFromArray([]));

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));
    }

    public function testCountingError(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred = new Deferred();

        $middleware->query(QueryBuilder::create(), function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred->reject(new \Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));
    }

    public function testCountingErrorSlo(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred = new Deferred();

        $middleware->query(QueryBuilder::create(), function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        sleep(2);

        $deferred->reject(new \Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 1,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));
    }
}
