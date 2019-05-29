<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use function ApiClients\Tools\Rx\observableFromArray;
use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use Prophecy\Prophecy\ObjectProphecy;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Client;

/**
 * @internal
 */
final class ClientTest extends AsyncTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $pgClient;

    /**
     * @var Client
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pgClient = $this->prophesize(PgClient::class);
        $this->client = new Client($this->pgClient->reveal());
    }

    public function testFetch(): void
    {
        $query = QueryBuilder::create()->select()->from('table')->where('id', '=', 1);

        $this->pgClient->executeStatement('SELECT * FROM "table" WHERE "id" = ?', [1])->shouldBeCalled()->willReturn(
            observableFromArray([
                [
                    'id' => 1,
                    'title' => 'Title',
                ],
            ])
        );

        $rows = $this->await($this->client->fetch($query)->toArray()->toPromise());

        self::assertCount(1, $rows);
    }
}
