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
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
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
        $this->client->query(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('FROM users', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
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
        $this->client->getRepository(CommentStub::class)->shouldNotBeCalled();

        $this->client->query(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(0, $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('COUNT(*) AS count', $query);
            self::assertStringNotContainsString('INNER JOIN users', $query);
            self::assertStringNotContainsString('LEFT JOIN comments', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
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
        $this->client->getRepository(CommentStub::class)->shouldNotBeCalled();

        $this->client->query(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(1, $builder->getParameters());
            self::assertSame(['98ce9eaf-b38b-4a51-93ed-131ffac4051e'], $builder->getParameters());
            $query = $builder->getQuery();
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('t1.id = t0.author_id', $query);
            self::assertStringContainsString('t2.id = t0.publisher_id', $query);
            self::assertStringContainsString('WHERE', $query);
            self::assertStringContainsString('t0.id = ?', $query);
            self::assertStringContainsString('ORDER BY', $query);
            self::assertStringContainsString('t0.id DESC', $query);

            // Assert the LEFT JOIN isn't happening
            self::assertStringNotContainsString('LEFT JOIN comments AS', $query);

            // Assert we're not loading in anything from the comments table
            self::assertStringNotContainsString('FROM comments', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
                [
                    't0___id' => '98ce9eaf-b38b-4a51-93ed-131ffac4051e',
                    't0___title' => 'blog_post_title',
                    't0___views' => '123',
                    't1___id' => '1a6cf50d-fa06-45ac-a510-375328f26541',
                    't1___name' => 'author_name',
                    't2___id' => '7bfdcadd-1e93-4c6e-9edf-d9bdf98a871c',
                    't2___name' => 'publisher_name',
                ],
        ]));

        /** @var ClientInterface $client */
        $client = $this->client->reveal();

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $client
        );

        /** @var BlogPostStub $blogPost */
        $blogPost = $this->await($repository->fetch([
            ['id', '=', '98ce9eaf-b38b-4a51-93ed-131ffac4051e',],
        ], [
            ['id', true,],
        ])->take(1)->toPromise());

        self::assertSame('98ce9eaf-b38b-4a51-93ed-131ffac4051e', $blogPost->getId());
        self::assertSame('blog_post_title', $blogPost->getTitle());
        self::assertSame(123, $blogPost->getViews());
        self::assertSame('1a6cf50d-fa06-45ac-a510-375328f26541', $blogPost->getAuthor()->getId());
        self::assertSame('author_name', $blogPost->getAuthor()->getName());
        self::assertSame('7bfdcadd-1e93-4c6e-9edf-d9bdf98a871c', $blogPost->getPublisher()->getId());
        self::assertSame('publisher_name', $blogPost->getPublisher()->getName());
    }

    public function testFetchWithJoinsLazyLoadComments(): void
    {
        /** @var ClientInterface $client */
        $client = $this->client->reveal();

        $this->client->getRepository(CommentStub::class)->shouldBeCalled()->willReturn(
            new Repository((new EntityInspector(new AnnotationReader()))->getEntity(CommentStub::class), $client)
        );

        $this->client->query(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(1, $builder->getParameters());
            self::assertSame(['99d00028-28d6-4194-b377-a0039b278c4d'], $builder->getParameters());
            $query = $builder->getQuery();

            if (\strpos($query, 'FROM blog_posts') === false) {
                return false;
            }

            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('t1.id = t0.author_id', $query);
            self::assertStringContainsString('t2.id = t0.publisher_id', $query);
            self::assertStringContainsString('WHERE', $query);
            self::assertStringContainsString('t0.id = ?', $query);
            self::assertStringContainsString('ORDER BY', $query);
            self::assertStringContainsString('t0.id DESC', $query);

            // Assert the LEFT JOIN isn't happening
            self::assertStringNotContainsString('LEFT JOIN comments AS', $query);

            // Assert we're not loading in anything from the comments table
            self::assertStringNotContainsString('FROM comments', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
                [
                    't0___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                    't0___title' => 'blog_post_title',
                    't1___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                    't1___name' => 'author_name',
                    't2___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                    't2___name' => 'publisher_name',
                ],
        ]));

        $this->client->query(Argument::that(function (QueryBuilder $builder) {
            self::assertCount(1, $builder->getParameters());
            self::assertSame(['99d00028-28d6-4194-b377-a0039b278c4d'], $builder->getParameters());
            $query = $builder->getQuery();

            if (\strpos($query, 'FROM comments') === false) {
                return false;
            }

            self::assertStringContainsString('FROM comments', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN users', $query);
            self::assertStringContainsString('t1.id = t0.author_id', $query);
            self::assertStringContainsString('WHERE', $query);
            self::assertStringContainsString('t0.blog_post_id = ?', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
                [
                    't0___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                    't0___title' => 'blog_post_title',
                    't0___contents' => 'comment contents',
                    't1___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                    't1___name' => 'author_name',
                ],
                [
                    't0___id' => 'fa41900d-4f62-4037-9eb3-8cfb4b90eeef',
                    't0___title' => 'blog_post_title',
                    't0___contents' => 'comment contents',
                    't1___id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                    't1___name' => 'author_name',
                ],
                [
                    't0___id' => '83f451cb-4b20-41b5-a8be-637af0bf1284',
                    't0___title' => 'blog_post_title',
                    't0___contents' => 'comment contents',
                    't1___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                    't1___name' => 'author_name',
                ],
                [
                    't0___id' => '590d4a9d-afb2-4860-a746-b0a086554064',
                    't0___title' => 'blog_post_title',
                    't0___contents' => 'comment contents',
                    't1___id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                    't1___name' => 'author_name',
                ],
        ]));

        $repository = new Repository(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            $client
        );

        /** @var BlogPostStub $blogPost */
        $blogPost = $this->await($repository->fetch([
            ['id', '=', '99d00028-28d6-4194-b377-a0039b278c4d',],
        ], [
            ['id', true,],
        ])->take(1)->toPromise());

        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $blogPost->getId());
        self::assertSame('blog_post_title', $blogPost->getTitle());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $blogPost->getAuthor()->getId());
        self::assertSame('author_name', $blogPost->getAuthor()->getName());
        self::assertSame('d45e8a1b-b962-4c1b-a7e7-c867fa06ffa7', $blogPost->getPublisher()->getId());
        self::assertSame('publisher_name', $blogPost->getPublisher()->getName());

        /** @var CommentStub[] $comments */
        $comments = $this->await($blogPost->getComments()->toArray()->toPromise());

        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $comments[0]->getId());
        self::assertSame('d45e8a1b-b962-4c1b-a7e7-c867fa06ffa7', $comments[0]->getAuthor()->getId());

        self::assertSame('fa41900d-4f62-4037-9eb3-8cfb4b90eeef', $comments[1]->getId());
        self::assertSame('0da49bee-ab27-4b24-a949-7b71a0b0449a', $comments[1]->getAuthor()->getId());

        self::assertSame('83f451cb-4b20-41b5-a8be-637af0bf1284', $comments[2]->getId());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $comments[2]->getAuthor()->getId());

        self::assertSame('590d4a9d-afb2-4860-a746-b0a086554064', $comments[3]->getId());
        self::assertSame('0da49bee-ab27-4b24-a949-7b71a0b0449a', $comments[3]->getAuthor()->getId());
    }
}
