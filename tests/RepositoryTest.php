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

        /** @var ClientInterface $client */
        $client = $this->client->reveal();

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(UserStub::class),
            $client
        );

        self::assertSame(123, $this->await($repository->count()));
    }

    public function testCountWithJoins(): void
    {
        $this->client->fetch(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);
            self::assertStringNotContainsString('INNER JOIN users', $query);
            self::assertStringNotContainsString('LEFT JOIN comments', $query);

            return true;
        }))->willReturn(observableFromArray([
            [
                'count' => '123',
            ],
            [
                'count' => '456',
            ],
        ]));

        /** @var ClientInterface $client */
        $client = $this->client->reveal();

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $client
        );

        self::assertSame(123, $this->await($repository->count()));
    }

    public function testFetchWithJoins(): void
    {
        $this->client->fetch(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('t1.id = t0.author_id', $query);
            self::assertStringContainsString('t2.id = t0.publisher_id', $query);
            self::assertStringNotContainsString('LEFT JOIN comments AS', $query);

            return true;
        }))->willReturn(observableFromArray([
                [
                    't0___id' => 1,
                    't0___title' => 'blog_post_title',
                    't1___id' => 3,
                    't1___name' => 'author_name',
                    't2___id' => 2,
                    't2___name' => 'publisher_name',
                ],
        ]));

        /** @var ClientInterface $client */
        $client = $this->client->reveal();

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $client
        );

        $blogPost = $this->await($repository->fetch()->take(1)->toPromise());

        self::assertInstanceOf(BlogPostStub::class, $blogPost);
        self::assertSame(1, $blogPost->getId());
        self::assertSame('blog_post_title', $blogPost->getTitle());
        self::assertSame(3, $blogPost->getAuthor()->getId());
        self::assertSame('author_name', $blogPost->getAuthor()->getName());
        self::assertSame(2, $blogPost->getPublisher()->getId());
        self::assertSame('publisher_name', $blogPost->getPublisher()->getName());
    }
}
