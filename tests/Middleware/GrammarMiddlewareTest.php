<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Plasma\SQL\GrammarInterface;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;
use Rx\ObservableInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\GrammarMiddleware;
use function ApiClients\Tools\Rx\observableFromArray;

/**
 * @internal
 */
final class GrammarMiddlewareTest extends AsyncTestCase
{
    public function testInsertedGrammar(): void
    {
        $grammar = $this->prophesize(GrammarInterface::class);
        $middleware = new GrammarMiddleware($grammar->reveal());

        $query = $this->prophesize(QueryBuilder::class);
        $query->withGrammar($grammar->reveal())->shouldBeCalled()->willReturn($query->reveal());

        $nextCount = false;
        $observable = $this->await($middleware->query($query->reveal(), function () use (&$nextCount) {
            $nextCount = true;

            return observableFromArray([]);
        }));

        self::assertTrue($nextCount);
        self::assertInstanceOf(ObservableInterface::class, $observable);
        self::assertInstanceOf(Observable::class, $observable);
    }
}
