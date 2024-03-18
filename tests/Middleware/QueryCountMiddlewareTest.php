<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Exception;
use Latitude\QueryBuilder\QueryFactory;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Rx\Observable;
use Rx\Subject\Subject;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;

use function sleep;

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
        ], [...$middleware->counters()]);

        $deferred = new Deferred();

        Observable::fromPromise($middleware->query((new QueryFactory())->select()->asExpression(), static function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->mergeAll()->subscribe(static function (): void {
        }, static function (): void {
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        $deferred->resolve(Observable::fromArray([[]]));

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$middleware->counters()]);

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);
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
        ], [...$middleware->counters()]);

        $deferred = new Deferred();

        Observable::fromPromise($middleware->query((new QueryFactory())->select()->asExpression(), static function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->mergeAll()->subscribe(static function (): void {
        }, static function (): void {
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        $subject = new Subject();
        $deferred->resolve($subject);
        $subject->onError(new Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);
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
        ], [...$middleware->counters()]);

        $deferred = new Deferred();

        Observable::fromPromise($middleware->query((new QueryFactory())->select()->asExpression(), static function () use ($deferred): PromiseInterface {
            return $deferred->promise();
        }))->mergeAll()->subscribe(static function (): void {
        }, static function (): void {
        });

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        sleep(2); /** @phpstan-ignore-line We're using blocking sleep here on purpose */

        $subject = new Subject();
        $deferred->resolve($subject);
        $subject->onError(new Exception('whoops'));

        self::assertSame([
            'initiated' => 1,
            'successful' => 0,
            'errored' => 1,
            'slow' => 1,
            'completed' => 0,
        ], [...$middleware->counters()]);

        $middleware->resetCounters();

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);
    }
}
