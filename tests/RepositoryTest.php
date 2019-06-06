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
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);
            self::assertStringNotContainsString('INNER JOIN users', $query);
            self::assertStringNotContainsString('users.id = blog_posts.author_id', $query);
            self::assertStringNotContainsString('users.id = comments.author_id', $query);
            self::assertStringNotContainsString('LEFT JOIN comments', $query);
            self::assertStringNotContainsString('comments.blog_post_id = CAST(blog_posts.id AS BIGINT)', $query);

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
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('t1.id = t0.author_id', $query);
            self::assertStringNotContainsString('LEFT JOIN', $query);
            self::assertStringNotContainsString('comments.blog_post_id = CAST(blog_posts.id AS BIGINT)', $query);

            return true;
        }))->willReturn(observableFromArray([
                [
                    't0___id' => 1,
                    't0___title' => 'blog_post_title',
                    't1___id' => 3,
                    't1___name' => 'author_name',
                ],
        ]));

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $this->client->reveal()
        );

        /** @var BlogPostStub $rows */
        $blogPost = $this->await($repository->fetch()->take(1)->toPromise());

        self::assertInstanceOf(BlogPostStub::class, $blogPost);
        self::assertSame(1, $blogPost->getId());
        self::assertSame('blog_post_title', $blogPost->getTitle());
        self::assertSame(3, $blogPost->getAuthor()->getId());
        self::assertSame('author_name', $blogPost->getAuthor()->getName());
    }
}
