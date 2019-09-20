<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;
use Rx\ObservableInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\ExecuteQueryMiddleware;
use function ApiClients\Tools\Rx\observableFromArray;

/**
 * @internal
 */
final class ExecuteQueryMiddlewareTest extends AsyncTestCase
{
    public function testExecutingQuery(): void
    {
        $client = $this->prophesize(PgClient::class);
        $client->executeStatement(
            'string',
            [
                'foo',
                'bar',
            ]
        )->shouldBeCalled()->willReturn(observableFromArray([
            'beer',
            'baz',
        ]));
        $middleware = new ExecuteQueryMiddleware($client->reveal());

        $query = $this->prophesize(QueryBuilder::class);
        $query->getQuery()->shouldBeCalled()->willReturn('string');
        $query->getParameters()->shouldBeCalled()->willReturn([
            'foo',
            'bar',
        ]);

        $observable = $this->await($middleware->query($query->reveal()));

        self::assertInstanceOf(ObservableInterface::class, $observable);
        self::assertInstanceOf(Observable::class, $observable);
        self::assertSame([
            'beer',
            'baz',
        ], $this->await($observable->toArray()->toPromise()));
    }
}
