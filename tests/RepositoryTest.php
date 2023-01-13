<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Latitude\QueryBuilder\Engine\PostgresEngine;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryFactory;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\Where;
use WyriHaximus\React\SimpleORM\Repository;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

use function ApiClients\Tools\Rx\observableFromArray;
use function assert;
use function Safe\date;
use function strpos;

final class RepositoryTest extends AsyncTestCase
{
    private ObjectProphecy $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->prophesize(ClientInterface::class);
    }

    public function testCount(): void
    {
        $this->client->query(Argument::that(static function (ExpressionInterface $expression): bool {
            self::assertCount(0, $expression->params(new PostgresEngine()));
            $query = $expression->sql(new PostgresEngine());
            self::assertStringContainsString('FROM "users"', $query);
            self::assertStringContainsString('COUNT(*) AS "count"', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
            ['count' => '123'],
        ]));

        $client = $this->client->reveal();
        assert($client instanceof ClientInterface);

        $repository = new Repository(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(UserStub::class),
            $client,
            new QueryFactory()
        );

        self::assertSame(123, $this->await($repository->count()));
    }

    public function testCountWithContraints(): void
    {
        $this->client->query(Argument::that(static function (ExpressionInterface $expression): bool {
            self::assertCount(1, $expression->params(new PostgresEngine()));
            $query = $expression->sql(new PostgresEngine());
            self::assertStringContainsString('FROM "users"', $query);
            self::assertStringContainsString('COUNT(*) AS "count"', $query);
            self::assertStringContainsString('WHERE "t0"."field" = ?', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
            ['count' => '123'],
        ]));

        $client = $this->client->reveal();
        assert($client instanceof ClientInterface);

        $repository = new Repository(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(UserStub::class),
            $client,
            new QueryFactory()
        );

        self::assertSame(123, $this->await($repository->count(
            new Where(
                new Where\Field('field', 'eq', ['values']),
            ),
        )));
    }

    public function testCountWithJoins(): void
    {
        $this->client->repository(CommentStub::class)->shouldNotBeCalled();

        $this->client->query(Argument::that(static function (ExpressionInterface $expression): bool {
            self::assertCount(0, $expression->params(new PostgresEngine()));
            $query = $expression->sql(new PostgresEngine());
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('COUNT(*) AS "count"', $query);
            self::assertStringNotContainsString('INNER JOIN users', $query);
            self::assertStringNotContainsString('LEFT JOIN comments', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
            ['count' => '123'],
            ['count' => '456'],
        ]));

        $client = $this->client->reveal();
        assert($client instanceof ClientInterface);

        $repository = new Repository(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(BlogPostStub::class),
            $client,
            new QueryFactory()
        );

        self::assertSame(123, $this->await($repository->count()));
    }

    public function testFetchWithJoins(): void
    {
        $this->client->repository(CommentStub::class)->shouldNotBeCalled();

        $this->client->query(Argument::that(static function (ExpressionInterface $expression): bool {
            self::assertCount(1, $expression->params(new PostgresEngine()));
            self::assertSame(['98ce9eaf-b38b-4a51-93ed-131ffac4051e'], $expression->params(new PostgresEngine()));
            $query = $expression->sql(new PostgresEngine());
            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('"t1"."id" = "t0"."author_id"', $query);
            self::assertStringContainsString('"t2"."id" = "t0"."publisher_id"', $query);
            self::assertStringContainsString('WHERE', $query);
            self::assertStringContainsString('"t0"."id" = ?', $query);
            self::assertStringContainsString('ORDER BY', $query);
            self::assertStringContainsString('"t0"."id" DESC', $query);

            // Assert the LEFT JOIN isn't happening
            self::assertStringNotContainsString('LEFT JOIN "comments" AS', $query);

            // Assert we're not loading in anything from the comments table
            self::assertStringNotContainsString('FROM "comments"', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
            [
                't0___id' => '98ce9eaf-b38b-4a51-93ed-131ffac4051e',
                't0___title' => 'blog_post_title',
                't0___views' => '123',
                't0___author_id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't0___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't0___contents' => 'comment contents',
                't0___created' => date('Y-m-d H:i:s e'),
                't0___modified' => date('Y-m-d H:i:s e'),
                't1___id' => '1a6cf50d-fa06-45ac-a510-375328f26541',
                't1___name' => 'author_name',
                't2___id' => '7bfdcadd-1e93-4c6e-9edf-d9bdf98a871c',
                't2___name' => 'publisher_name',
            ],
        ]));

        $client = $this->client->reveal();
        assert($client instanceof ClientInterface);

        $repository = new Repository(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(BlogPostStub::class),
            $client,
            new QueryFactory()
        );

        $blogPost = $this->await($repository->fetch(new Where(
            new Where\Field('id', 'eq', ['98ce9eaf-b38b-4a51-93ed-131ffac4051e']),
        ), new Order(
            new Order\Desc('id'),
        ))->take(1)->toPromise());
        assert($blogPost instanceof BlogPostStub);

        self::assertSame('98ce9eaf-b38b-4a51-93ed-131ffac4051e', $blogPost->id());
        self::assertSame('blog_post_title', $blogPost->getTitle());
        self::assertSame(123, $blogPost->getViews());
        self::assertSame('1a6cf50d-fa06-45ac-a510-375328f26541', $blogPost->getAuthor()->id());
        self::assertSame('author_name', $blogPost->getAuthor()->getName());
        self::assertSame('7bfdcadd-1e93-4c6e-9edf-d9bdf98a871c', $blogPost->getPublisher()->id());
        self::assertSame('publisher_name', $blogPost->getPublisher()->getName());
    }

    public function testFetchWithJoinsLazyLoadComments(): void
    {
        $client = $this->client->reveal();
        assert($client instanceof ClientInterface);

        $this->client->repository(CommentStub::class)->shouldBeCalled()->willReturn(
            new Repository((new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(CommentStub::class), $client, new QueryFactory())
        );

        $this->client->query(Argument::that(static function (ExpressionInterface $expression): bool {
            self::assertCount(1, $expression->params(new PostgresEngine()));
            self::assertSame(['99d00028-28d6-4194-b377-a0039b278c4d'], $expression->params(new PostgresEngine()));
            $query = $expression->sql(new PostgresEngine());

            if (strpos($query, 'FROM "blog_posts"') === false) {
                return false;
            }

            self::assertStringContainsString('blog_posts', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN', $query);
            self::assertStringContainsString('"t1"."id" = "t0"."author_id"', $query);
            self::assertStringContainsString('"t2"."id" = "t0"."publisher_id"', $query);
            self::assertStringContainsString('WHERE', $query);
            self::assertStringContainsString('"t0"."id" = ?', $query);
            self::assertStringContainsString('ORDER BY', $query);
            self::assertStringContainsString('"t0"."id" DESC', $query);

            // Assert the LEFT JOIN isn't happening
            self::assertStringNotContainsString('LEFT JOIN "comments" AS', $query);

            // Assert we're not loading in anything from the comments table
            self::assertStringNotContainsString('FROM "comments"', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
            [
                't0___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't0___author_id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't0___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't0___title' => 'blog_post_title',
                't0___contents' => 'comment contents',
                't0___views' => 1337,
                't0___created' => date('Y-m-d H:i:s e'),
                't0___modified' => date('Y-m-d H:i:s e'),
                't1___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't1___name' => 'author_name',
                't2___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't2___name' => 'publisher_name',
            ],
        ]));

        $this->client->query(Argument::that(static function (ExpressionInterface $expression): bool {
            self::assertCount(1, $expression->params(new PostgresEngine()));
            self::assertSame(['99d00028-28d6-4194-b377-a0039b278c4d'], $expression->params(new PostgresEngine()));
            $query = $expression->sql(new PostgresEngine());

            if (strpos($query, 'FROM "comments"') === false) {
                return false;
            }

            self::assertStringContainsString('FROM "comments"', $query);
            self::assertStringContainsString('users', $query);
            self::assertStringContainsString('INNER JOIN "users"', $query);
            self::assertStringContainsString('"t1"."id" = "t0"."author_id"', $query);
            self::assertStringContainsString('WHERE', $query);
            self::assertStringContainsString('"t0"."blog_post_id" = ?', $query);

            return true;
        }))->shouldBeCalled()->willReturn(observableFromArray([
            [
                't0___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't0___author_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't0___blog_post_id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't0___contents' => 'comment contents',
                't1___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't1___name' => 'author_name',
                't2___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't2___title' => 'blog_post_title',
                't2___author_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't2___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't2___contents' => 'comment contents',
                't2___views' => 1337,
                't2___created' => date('Y-m-d H:i:s e'),
                't2___modified' => date('Y-m-d H:i:s e'),
                't3___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't3___name' => 'author_name',
                't4___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't4___name' => 'publisher_name',
            ],
            [
                't0___id' => 'fa41900d-4f62-4037-9eb3-8cfb4b90eeef',
                't0___author_id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                't0___blog_post_id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't0___contents' => 'comment contents',
                't1___id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                't1___name' => 'author_name',
                't2___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't2___title' => 'blog_post_title',
                't2___author_id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                't2___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't2___contents' => 'comment contents',
                't2___views' => 1337,
                't2___created' => date('Y-m-d H:i:s e'),
                't2___modified' => date('Y-m-d H:i:s e'),
                't3___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't3___name' => 'author_name',
                't4___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't4___name' => 'publisher_name',
            ],
            [
                't0___id' => '83f451cb-4b20-41b5-a8be-637af0bf1284',
                't0___author_id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't0___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't0___blog_post_id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't0___contents' => 'comment contents',
                't1___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't1___name' => 'author_name',
                't2___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't2___title' => 'blog_post_title',
                't2___author_id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't2___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't2___contents' => 'comment contents',
                't2___views' => 1337,
                't2___created' => date('Y-m-d H:i:s e'),
                't2___modified' => date('Y-m-d H:i:s e'),
                't3___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't3___name' => 'author_name',
                't4___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't4___name' => 'publisher_name',
            ],
            [
                't0___id' => '590d4a9d-afb2-4860-a746-b0a086554064',
                't0___author_id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                't0___blog_post_id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't0___contents' => 'comment contents',
                't1___id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                't1___name' => 'author_name',
                't2___id' => '99d00028-28d6-4194-b377-a0039b278c4d',
                't2___title' => 'blog_post_title',
                't2___author_id' => '0da49bee-ab27-4b24-a949-7b71a0b0449a',
                't2___publisher_id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't2___contents' => 'comment contents',
                't2___views' => 1337,
                't2___created' => date('Y-m-d H:i:s e'),
                't2___modified' => date('Y-m-d H:i:s e'),
                't3___id' => '3fbf8eec-8a3f-4b01-ba9a-355f6650644b',
                't3___name' => 'author_name',
                't4___id' => 'd45e8a1b-b962-4c1b-a7e7-c867fa06ffa7',
                't4___name' => 'publisher_name',
            ],
        ]));

        $repository = new Repository(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(BlogPostStub::class),
            $client,
            new QueryFactory()
        );

        $blogPost = $this->await($repository->fetch(new Where(
            new Where\Field('id', 'eq', ['99d00028-28d6-4194-b377-a0039b278c4d']),
        ), new Order(
            new Order\Desc('id'),
        ))->take(1)->toPromise());
        assert($blogPost instanceof BlogPostStub);

        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $blogPost->id());
        self::assertSame('blog_post_title', $blogPost->getTitle());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $blogPost->getAuthor()->id());
        self::assertSame('author_name', $blogPost->getAuthor()->getName());
        self::assertSame('d45e8a1b-b962-4c1b-a7e7-c867fa06ffa7', $blogPost->getPublisher()->id());
        self::assertSame('publisher_name', $blogPost->getPublisher()->getName());

        /** @var CommentStub[] $comments */
        $comments = $this->await($blogPost->getComments()->toArray()->toPromise());

        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $comments[0]->id());
        self::assertSame('d45e8a1b-b962-4c1b-a7e7-c867fa06ffa7', $comments[0]->getAuthor()->id());
        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $comments[0]->getBlogPost()->id());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $comments[0]->getBlogPost()->getAuthor()->id());

        self::assertSame('fa41900d-4f62-4037-9eb3-8cfb4b90eeef', $comments[1]->id());
        self::assertSame('0da49bee-ab27-4b24-a949-7b71a0b0449a', $comments[1]->getAuthor()->id());
        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $comments[1]->getBlogPost()->id());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $comments[1]->getBlogPost()->getAuthor()->id());

        self::assertSame('83f451cb-4b20-41b5-a8be-637af0bf1284', $comments[2]->id());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $comments[2]->getAuthor()->id());
        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $comments[2]->getBlogPost()->id());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $comments[2]->getBlogPost()->getAuthor()->id());

        self::assertSame('590d4a9d-afb2-4860-a746-b0a086554064', $comments[3]->id());
        self::assertSame('0da49bee-ab27-4b24-a949-7b71a0b0449a', $comments[3]->getAuthor()->id());
        self::assertSame('99d00028-28d6-4194-b377-a0039b278c4d', $comments[3]->getBlogPost()->id());
        self::assertSame('3fbf8eec-8a3f-4b01-ba9a-355f6650644b', $comments[3]->getBlogPost()->getAuthor()->id());
    }
}
