<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Latitude\QueryBuilder\Partial\Criteria;
use Latitude\QueryBuilder\QueryFactory;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use function ApiClients\Tools\Rx\observableFromArray;
use Doctrine\Common\Annotations\Reader;
use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;
use function Latitude\QueryBuilder\field;

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
     * @var ObjectProphecy
     */
    private $annotationReader;

    /**
     * @var Client
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pgClient = $this->prophesize(PgClient::class);
        $this->annotationReader = $this->prophesize(Reader::class);
        $this->client = Client::createWithAnnotationReader(new Postgres($this->pgClient->reveal()), $this->annotationReader->reveal());
    }

    public function testGetRepository(): void
    {
        $this->annotationReader->getClassAnnotation(
            Argument::type(ReflectionClass::class),
            Table::class
        )->shouldBeCalled()->willReturn(new Table(['users']));

        $this->annotationReader->getClassAnnotations(
            Argument::type(ReflectionClass::class)
        )->shouldBeCalled()->willReturn([
            new Table(['users']),
        ]);

        $this->client->getRepository(UserStub::class);
    }

    public function testFetch(): void
    {
        $query = (new QueryFactory())->select()->from('table')->where(field('id')->eq(1))->asExpression();

        $this->pgClient->executeStatement('SELECT * FROM "table" WHERE "id" = $1', [1])->shouldBeCalled()->willReturn(
            observableFromArray([
                [
                    'id' => 1,
                    'title' => 'Title',
                ],
            ])
        );

        $rows = $this->await($this->client->query($query)->toArray()->toPromise());

        self::assertCount(1, $rows);
    }
}
