<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PgAsync\Client as PgClient;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

/**
 * @internal
 */
final class FunctionalTest extends AsyncTestCase
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ClientInterface
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loop = Factory::create();
        $this->client = new Client(
            new PgClient(
                [
                    'host' => 'localhost',
                    'port' => 5432,
                    'user'     => \getenv('PHINX_DB_USER'),
                    'password' => \getenv('PHINX_DB_PASSWORD'),
                    'database' => \getenv('PHINX_DB_DATABASE'),
                ],
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function usersCount(): void
    {
        self::assertSame(
            3,
            $this->await(
                $this->client->getRepository(UserStub::class)->count(),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function usersCountResultSet(): void
    {
        self::assertCount(
            3,
            $this->await(
                $this->client->getRepository(UserStub::class)->fetch()->toArray()->toPromise(),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function blogPostsCount(): void
    {
        self::assertSame(
            2,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->count(),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function blogPostsCountResultSet(): void
    {
        self::assertCount(
            2,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->toArray()->toPromise(),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function firstBlogPostCommentCount(): void
    {
        self::assertCount(
            2,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getComments()->toArray()->toPromise();
                }),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function firstBlogPostAuthorId(): void
    {
        self::assertSame(
            1,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getAuthor()->getId();
                }),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function firstBlogPostCommentAuthorIds(): void
    {
        self::assertSame(
            [
                3,
                2,
            ],
            \array_values(
                \array_map(
                    function (CommentStub $comment) {
                        return $comment->getAuthor()->getId();
                    },
                    $this->await(
                        $this->client->getRepository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(function (BlogPostStub $blogPost) {
                            return $blogPost->getComments()->toArray()->toPromise();
                        }),
                        $this->loop
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function secondBlogPostCommentCount(): void
    {
        self::assertCount(
            1,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                    return $blogPost->getId() === 2;
                })->toPromise()->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getComments()->toArray()->toPromise();
                }),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function secondBlogPostAuthorId(): void
    {
        self::assertSame(
            2,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                    return $blogPost->getId() === 2;
                })->toPromise()->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getAuthor()->getId();
                }),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function secondBlogPostCommentAuthorIds(): void
    {
        self::assertSame(
            [
                1,
            ],
            \array_values(
                \array_map(
                    function (CommentStub $comment) {
                        return $comment->getAuthor()->getId();
                    },
                    $this->await(
                        $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                            return $blogPost->getId() === 2;
                        })->toPromise()->then(function (BlogPostStub $blogPost) {
                            return $blogPost->getComments()->toArray()->toPromise();
                        }),
                        $this->loop
                    )
                )
            )
        );
    }
}
