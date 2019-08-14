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

        \exec('PHINX_DB_HOST=localhost php ./vendor/bin/phinx rollback');
        \exec('PHINX_DB_HOST=localhost php ./vendor/bin/phinx migrate');
        \exec('PHINX_DB_HOST=localhost php ./vendor/bin/phinx seed:run -v');

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
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
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
                '2fa0d077-d374-4409-b1ef-9687c6729158',
                '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
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
                    return $blogPost->getId() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
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
            '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                    return $blogPost->getId() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
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
                'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            ],
            \array_values(
                \array_map(
                    function (CommentStub $comment) {
                        return $comment->getAuthor()->getId();
                    },
                    $this->await(
                        $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                            return $blogPost->getId() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                        })->toPromise()->then(function (BlogPostStub $blogPost) {
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
    public function secondBlogPostPreviousBlogPostAuthorId(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                    return $blogPost->getId() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getPreviousBlogPost();
                })->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getAuthor()->getId();
                }),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function secondBlogPostNextBlogPostResolvesToNull(): void
    {
        self::assertSame(
            null,
            $this->await(
                $this->client->getRepository(BlogPostStub::class)->fetch()->filter(function (BlogPostStub $blogPost): bool {
                    return $blogPost->getId() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(function (BlogPostStub $blogPost) {
                    return $blogPost->getNextBlogPost();
                }),
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function createUser(): void
    {
        $name = 'Commander Fuzzy paws';

        $fields = [
            'name' => $name,
        ];

        $user = $this->await(
            $this->client->getRepository(UserStub::class)->create($fields),
            $this->loop
        );

        self::assertSame($name, $user->getName());
    }

    /**
     * @test
     */
    public function increaseViews(): void
    {
        \sleep(3);

        $repository = $this->client->getRepository(BlogPostStub::class);

        /** @var BlogPostStub $originalBlogPost */
        $originalBlogPost = null;

        /** @var int $timestamp */
        $timestamp = null;

        /** @var BlogPostStub $updatedBlogPost */
        $updatedBlogPost = $this->await(
            $repository->fetch()->takeLast(1)->toPromise()->then(function (BlogPostStub $blogPost) use (&$originalBlogPost, &$timestamp) {
                self::waitUntilTheNextSecond();

                $originalBlogPost = $blogPost;
                $timestamp = \time();

                return $blogPost->withViews($blogPost->getViews() + 1);
            })->then(function (BlogPostStub $blogPost) use ($repository) {
                return $repository->update($blogPost);
            }),
            $this->loop
        );

        self::assertSame(167, $updatedBlogPost->getViews());
        self::assertSame($originalBlogPost->getCreated()->format('U'), $updatedBlogPost->getCreated()->format('U'));
        self::assertGreaterThan($originalBlogPost->getModified(), $updatedBlogPost->getModified());
        self::assertSame($timestamp, (int)$updatedBlogPost->getModified()->format('U'));
    }
}
