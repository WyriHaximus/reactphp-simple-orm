<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\Reader;
use Latitude\QueryBuilder\QueryFactory;
use PgAsync\Client as PgClient;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use Rx\Observable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

use function Latitude\QueryBuilder\field;
use function React\Async\await;

final class ClientTest extends AsyncTestCase
{
    private ObjectProphecy $pgClient;

    private ObjectProphecy $annotationReader;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pgClient         = $this->prophesize(PgClient::class);
        $this->annotationReader = $this->prophesize(Reader::class);
        $this->client           = Client::createWithAnnotationReader(new Postgres($this->pgClient->reveal()), $this->annotationReader->reveal());
    }

    public function testGetRepository(): void
    {
        $this->annotationReader->getClassAnnotation(
            Argument::type(ReflectionClass::class),
            Table::class,
        )->shouldBeCalled()->willReturn(new Table(['users']));

        $this->annotationReader->getClassAnnotations(
            Argument::type(ReflectionClass::class),
        )->shouldBeCalled()->willReturn([
            new Table(['users']),
        ]);

        $this->client->repository(UserStub::class);
    }

    public function testFetch(): void
    {
        $query = (new QueryFactory())->select()->from('table')->where(field('id')->eq(1))->asExpression();

        $this->pgClient->executeStatement('SELECT * FROM "table" WHERE "id" = $1', [1])->shouldBeCalled()->willReturn(
            Observable::fromArray([
                [
                    'id' => 1,
                    'title' => 'Title',
                ],
            ]),
        );

        $rows = await($this->client->query($query)->toArray()->toPromise());

        self::assertCount(1, $rows);
    }
}
