<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Exception;
use Latitude\QueryBuilder\QueryFactory;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Rx\Subject\Subject;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
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
        ], iteratorOrArrayToArray($middleware->counters()));

        $deferred = new Deferred();

        unwrapObservableFromPromise($middleware->query((new QueryFactory())->select()->asExpression(), static function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->subscribe(static function (): void {
        }, static function (): void {
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));

        $deferred->resolve(observableFromArray([[]]));

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($middleware->counters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));
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
        ], iteratorOrArrayToArray($middleware->counters()));

        $deferred = new Deferred();

        unwrapObservableFromPromise($middleware->query((new QueryFactory())->select()->asExpression(), static function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->subscribe(static function (): void {
        }, static function (): void {
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));

        $subject = new Subject();
        $deferred->resolve($subject);
        $subject->onError(new Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));
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
        ], iteratorOrArrayToArray($middleware->counters()));

        $deferred = new Deferred();

        unwrapObservableFromPromise($middleware->query((new QueryFactory())->select()->asExpression(), static function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->subscribe(static function (): void {
        }, static function (): void {
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));

        sleep(2);

        $subject = new Subject();
        $deferred->resolve($subject);
        $subject->onError(new Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 1,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], iteratorOrArrayToArray($middleware->counters()));
    }
}
