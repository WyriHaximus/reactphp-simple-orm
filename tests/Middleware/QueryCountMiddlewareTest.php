<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;
use Rx\ObservableInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\ExecuteQueryMiddleware;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use function ApiClients\Tools\Rx\observableFromArray;

/**
 * @internal
 */
final class QueryCountMiddlewareTest extends AsyncTestCase
{
    public function testCounting(): void
    {
        $middleware = new QueryCountMiddleware();

        self::assertSame(0, $middleware->getCount());

        $middleware->query(QueryBuilder::create(), function () {});

        self::assertSame(1, $middleware->getCount());

        $middleware->resetCount();

        self::assertSame(0, $middleware->getCount());
    }
}
