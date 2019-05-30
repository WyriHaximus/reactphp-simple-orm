<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use function ApiClients\Tools\Rx\observableFromArray;
use Plasma\SQL\QueryBuilder;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\Repository;

/**
 * @internal
 */
final class RepositoryTest extends AsyncTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->prophesize(ClientInterface::class);
    }

    public function testCount(): void
    {
        $this->client->fetch(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('tables', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);

            return true;
        }))->willReturn(observableFromArray([
            [
                'count' => '123',
            ],
        ]));

        $repository = new Repository($this->client->reveal(), EntityStub::class);

        self::assertSame(123, $this->await($repository->count()));
    }
}
