<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use function ApiClients\Tools\Rx\observableFromArray;
use Doctrine\Common\Annotations\AnnotationReader;
use Plasma\SQL\QueryBuilder;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\Repository;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

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
            self::assertStringContainsString('FROM users', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);

            return true;
        }))->willReturn(observableFromArray([
            [
                'count' => '123',
            ],
        ]));

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(UserStub::class),
            $this->client->reveal()
        );

        self::assertSame(123, $this->await($repository->count()));
    }

    public function testCountWithJoins(): void
    {
        $this->client->fetch(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('tables', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('tables.id = CAST(table_with_joins.id AS VARCHAR)', $query);
            self::assertStringContainsString('LEFT JOIN', $query);
            self::assertStringContainsString('tables.id = CAST(table_with_joins.id AS BJIGINT)', $query);
            self::assertStringContainsString('RIGHT JOIN', $query);
            self::assertStringContainsString('CAST(tables.id AS VARCHAR) = table_with_joins.id', $query);

            return true;
        }))->willReturn(observableFromArray([
            [
                'count' => '123',
            ],
        ]));

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $this->client->reveal()
        );

        self::assertSame(123, $this->await($repository->count()));
    }

    public function testFetchWithJoins(): void
    {
        $this->client->fetch(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('tables', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('tables.id = CAST(table_with_joins.id AS VARCHAR)', $query);
            self::assertStringContainsString('LEFT JOIN', $query);
            self::assertStringContainsString('tables.id = CAST(table_with_joins.id AS BJIGINT)', $query);
            self::assertStringContainsString('RIGHT JOIN', $query);
            self::assertStringContainsString('CAST(tables.id AS VARCHAR) = table_with_joins.id', $query);

            return true;
        }))->willReturn(observableFromArray([
                [
                    'table_with_joins___id' => 1,
                    'table_with_joins___foreign_id' => 2,
                    'table_with_joins___title' => 'table_with_joins.fields',
                    'tables___id' => 3,
                    'tables___title' => 'tables.title',
                ],
        ]));

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $this->client->reveal()
        );

        $rows = $this->await($repository->fetch()->toArray()->toPromise());

        /** @var EntityWithJoinStub $row */
        $row = current($rows);

        self::assertCount(1, $rows);
        self::assertInstanceOf(BlogPostStub::class, $row);
        self::assertSame(1, $row->getId());
        self::assertSame(2, $row->getForeignId());
        self::assertSame('table_with_joins.fields', $row->getTitle());
    }
}
