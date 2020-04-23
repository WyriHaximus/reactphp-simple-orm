<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Latitude\QueryBuilder\QueryFactory;
use Plasma\SQL\QueryBuilder;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Rx\Subject\Subject;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\ExecuteQueryMiddleware;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use function ApiClients\Tools\Rx\observableFromArray;
use function ApiClients\Tools\Rx\unwrapObservableFromPromise;
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
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred = new Deferred();

        unwrapObservableFromPromise($middleware->query((new QueryFactory())->select()->asExpression(), function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->subscribe(function () {}, function () {});

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred->resolve(observableFromArray([[]]));

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
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
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred = new Deferred();

        unwrapObservableFromPromise($middleware->query((new QueryFactory())->select()->asExpression(), function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->subscribe(function () {}, function () {});

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $subject = new Subject();
        $deferred->resolve($subject);
        $subject->onError(new \Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));
    }

    public function testCountingErrorSlow(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $deferred = new Deferred();

        unwrapObservableFromPromise($middleware->query((new QueryFactory())->select()->asExpression(), function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->subscribe(function () {}, function () {});

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        sleep(2);

        $subject = new Subject();
        $deferred->resolve($subject);
        $subject->onError(new \Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 1,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->getCounters()));
    }
}
