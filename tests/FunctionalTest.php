<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PgAsync\Client as PgClient;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Safe\DateTimeImmutable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

use function array_map;
use function array_values;
use function assert;
use function bin2hex;
use function exec;
use function getenv;
use function random_bytes;
use function Safe\sleep;
use function time;
use function WyriHaximus\iteratorOrArrayToArray;

final class FunctionalTest extends AsyncTestCase
{
    private const AWAIT_TIMEOUT = 66.6;

    private LoopInterface $loop;

    private ClientInterface $client;

    private QueryCountMiddleware $counter;

    protected function setUp(): void
    {
        parent::setUp();

        exec('php ./vendor/bin/phinx rollback');
        exec('php ./vendor/bin/phinx migrate');
        exec('php ./vendor/bin/phinx seed:run -v');

        $this->counter = new QueryCountMiddleware(1);

        $this->loop   = Factory::create();
        $this->client = Client::create(
            new Postgres(
                new PgClient(
                    [
                        'host' => getenv('PHINX_DB_HOST'),
                        'port' => 5432,
                        'user'     => getenv('PHINX_DB_USER'),
                        'password' => getenv('PHINX_DB_PASSWORD'),
                        'database' => getenv('PHINX_DB_DATABASE'),
                    ],
                    $this->loop
                )
            ),
            $this->counter
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
                $this->client->repository(UserStub::class)->count(),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function usersCountResultSet(): void
    {
        self::assertCount(
            3,
            $this->await(
                $this->client->repository(UserStub::class)->fetch()->toArray()->toPromise(),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function blogPostsCount(): void
    {
        self::assertSame(
            2,
            $this->await(
                $this->client->repository(BlogPostStub::class)->count(),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function blogPostsCountResultSet(): void
    {
        self::assertCount(
            2,
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->toArray()->toPromise(),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function firstBlogPostCommentCount(): void
    {
        self::assertCount(
            2,
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getComments()->toArray()->toPromise();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function firstBlogPostAuthorId(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function firstBlogPostAuthorIdUsingLimit(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch(null, null, 1)->toPromise()->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
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
            array_values(
                array_map(
                    static function (CommentStub $comment): string {
                        return $comment->getAuthor()->id();
                    },
                    $this->await(
                        $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                            return $blogPost->getComments()->toArray()->toPromise();
                        }),
                        $this->loop,
                        self::AWAIT_TIMEOUT
                    )
                )
            )
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function firstBlogPostNextBlogPostResolvesToBlogPost(): void
    {
        self::assertInstanceOf(
            BlogPostStub::class,
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getNextBlogPost();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function firstBlogPostPreviousBlogPostResolvesToNull(): void
    {
        self::assertNull(
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getPreviousBlogPost();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function secondBlogPostCommentCount(): void
    {
        self::assertCount(
            1,
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getComments()->toArray()->toPromise();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function secondBlogPostAuthorId(): void
    {
        self::assertSame(
            '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function secondBlogPostCommentAuthorIds(): void
    {
        self::assertSame(
            ['fb175cbc-04cc-41c7-8e35-6b817ac016ca'],
            array_values(
                array_map(
                    static function (CommentStub $comment): string {
                        return $comment->getAuthor()->id();
                    },
                    $this->await(
                        $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                            return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                        })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                            return $blogPost->getComments()->toArray()->toPromise();
                        }),
                        $this->loop,
                        self::AWAIT_TIMEOUT
                    )
                )
            )
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function secondBlogPostPreviousBlogPostAuthorId(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getPreviousBlogPost();
                })->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function secondBlogPostNextBlogPostResolvesToNull(): void
    {
        self::assertNull(
            $this->await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getNextBlogPost();
                }),
                $this->loop,
                self::AWAIT_TIMEOUT
            )
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function createUser(): void
    {
        $name = 'Commander Fuzzy paws';

        $fields = ['name' => $name];

        $user = $this->await(
            $this->client->repository(UserStub::class)->create($fields),
            $this->loop,
            self::AWAIT_TIMEOUT
        );

        self::assertSame($name, $user->getName());
        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }

    /**
     * @test
     */
    public function increaseViews(): void
    {
        sleep(3);
        self::waitUntilTheNextSecond();

        $repository = $this->client->repository(BlogPostStub::class);

        $originalBlogPost = null;
        $timestamp        = null;
        $randomContents   = bin2hex(random_bytes(13));

        $updatedBlogPost = $this->await(
            $repository->fetch()->takeLast(1)->toPromise()->then(static function (BlogPostStub $blogPost) use (&$originalBlogPost, &$timestamp, $randomContents): BlogPostStub {
                self::waitUntilTheNextSecond();

                $originalBlogPost = $blogPost;
                $timestamp        = time();

                return $blogPost->withViews($blogPost->getViews() + 1)->withFields(['contents' => $randomContents, 'id' => 'nah', 'created' => new DateTimeImmutable(), 'modified' => new DateTimeImmutable()]);
            })->then(static function (BlogPostStub $blogPost) use ($repository): PromiseInterface {
                return $repository->update($blogPost);
            }),
            $this->loop,
            self::AWAIT_TIMEOUT
        );

        assert($originalBlogPost instanceof BlogPostStub);
        assert($updatedBlogPost instanceof BlogPostStub);

        self::assertSame(167, $updatedBlogPost->getViews());
        self::assertSame($originalBlogPost->id(), $updatedBlogPost->id());
        self::assertSame($originalBlogPost->getCreated()->format('U'), $updatedBlogPost->getCreated()->format('U'));
        self::assertGreaterThan($originalBlogPost->getModified(), $updatedBlogPost->getModified());
        self::assertSame($timestamp, (int) $updatedBlogPost->getModified()->format('U'));
        self::assertSame([
            'initiated' => 3,
            'successful' => 3,
            'errored' => 0,
            'slow' => 0,
            'completed' => 3,
        ], iteratorOrArrayToArray($this->counter->counters()));
        self::assertNotSame($originalBlogPost->getContents(), $updatedBlogPost->getContents());
        self::assertSame($updatedBlogPost->getContents(), $randomContents);
    }

    /**
     * @test
     */
    public function userSelf(): void
    {
        $repository = $this->client->repository(UserStub::class);

        $userId = null;

        $self = $this->await($repository->fetch()->take(1)->toPromise()->then(static function (UserStub $user) use (&$userId): PromiseInterface {
            $userId = $user->id();

            return $user->getZelf();
        }), $this->loop, self::AWAIT_TIMEOUT);

        self::assertNotNull($userId);
        self::assertNotNull($self);
        self::assertSame($userId, $self->id());
        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], iteratorOrArrayToArray($this->counter->counters()));
    }
}
