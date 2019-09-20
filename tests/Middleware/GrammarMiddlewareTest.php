<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Middleware;

use Plasma\SQL\GrammarInterface;
use Plasma\SQL\QueryBuilder;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Middleware\GrammarMiddleware;

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

        $queryWithGrammar = $this->await($middleware->query($query->reveal()));

        self::assertSame($query->reveal(), $queryWithGrammar);
    }
}
