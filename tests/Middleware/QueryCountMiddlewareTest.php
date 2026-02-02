<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Exception;
use Latitude\QueryBuilder\QueryFactory;
use PHPUnit\Framework\Attributes\Test;
use Throwable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;

use function React\Async\await;
use function React\Promise\Timer\sleep;

final class QueryCountMiddlewareTest extends AsyncTestCase
{
    #[Test]
    public function countingSuccess(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        foreach (
            $middleware->query(new QueryFactory()->select()->asExpression(), static function (): iterable {
                yield 1;
            }) as $row
        ) {
            self::assertSame([
                'initiated' => 1,
                'successful' => 0,
                'errored' => 0,
                'slow' => 0,
                'completed' => 0,
            ], [...$middleware->counters()]);
        }

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

    #[Test]
    public function countingError(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        try {
            foreach (
                $middleware->query(new QueryFactory()->select()->asExpression(), static function (): iterable {
                    yield 1;

                    throw new Exception('whoops');
                }) as $row
            ) {
                self::assertSame([
                    'initiated' => 1,
                    'successful' => 0,
                    'errored' => 0,
                    'slow' => 0,
                    'completed' => 0,
                ], [...$middleware->counters()]);
            }
        } catch (Throwable) {
            // Swallow exception
        }

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

    #[Test]
    public function countingErrorSlow(): void
    {
        $middleware = new QueryCountMiddleware(1);

        self::assertSame([
            'initiated' => 0,
            'successful' => 0,
            'errored' => 0,
            'slow' => 0,
            'completed' => 0,
        ], [...$middleware->counters()]);

        try {
            foreach (
                $middleware->query(new QueryFactory()->select()->asExpression(), static function (): iterable {
                    await(sleep(2));
                    yield 1;

                    throw new Exception('whoops');
                }) as $row
            ) {
                self::assertSame([
                    'initiated' => 1,
                    'successful' => 0,
                    'errored' => 0,
                    'slow' => 0,
                    'completed' => 0,
                ], [...$middleware->counters()]);
            }
        } catch (Throwable) {
            // Swallow exception
        }

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
}
