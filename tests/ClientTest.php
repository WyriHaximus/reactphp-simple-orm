<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Latitude\QueryBuilder\QueryFactory;
use Mockery;
use Mockery\MockInterface;
use PgAsync\Client as PgClient;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Rx\Observable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

use function Latitude\QueryBuilder\field;

final class ClientTest extends AsyncTestCase
{
    private MockInterface&PgClient $pgClient;

    private Client $client;

    #[Before]
    public function setupMocks(): void
    {
        $this->pgClient = Mockery::mock(PgClient::class);
        $this->client   = Client::create(new Postgres($this->pgClient), new Configuration(''));
    }

    #[Test]
    public function getRepository(): void
    {
        $this->client->repository(UserStub::class);
    }

    #[Test]
    public function fetch(): void
    {
        $query = new QueryFactory()->select()->from('table')->where(field('id')->eq(1))->asExpression();

        $this->pgClient->shouldReceive('executeStatement')
            ->once()
            ->with('SELECT * FROM "table" WHERE "id" = $1', [1])
            ->andReturn(
                Observable::fromArray([
                    [
                        'id' => 1,
                        'title' => 'Title',
                    ],
                ]),
            );

        $rows = [...$this->client->query($query)];

        self::assertCount(1, $rows);
    }
}
