<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PgAsync\Client as PgClient;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;
use Safe\DateTimeImmutable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use WyriHaximus\React\SimpleORM\Query\Limit;
use WyriHaximus\React\SimpleORM\Query\Where;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\LogStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

use function array_map;
use function array_values;
use function assert;
use function bin2hex;
use function random_bytes;
use function React\Async\await;
use function sleep;
use function time;

final class FunctionalTest extends AsyncTestCase
{
    private ClientInterface $client;

    private QueryCountMiddleware $counter;

    protected function setUp(): void
    {
        parent::setUp();

//        exec('php ./vendor/bin/phinx rollback');
//        exec('php ./vendor/bin/phinx migrate');
//        exec('php ./vendor/bin/phinx seed:run -v');

        $this->counter = new QueryCountMiddleware(1);

        $this->client = Client::create(
            new Postgres(
                new PgClient(
                    [
                        'host' => 'localhost',
                        'port' => 55432,
                        'user'     => 'postgres',
                        'password' => 'postgres',
                        'database' => 'postgres',
                    ],
                    Loop::get(),
                ),
            ),
            new Configuration(''),
            $this->counter,
        );
    }

    /** @test */
    public function usersCount(): void
    {
        self::assertSame(
            3,
            await(
                $this->client->repository(UserStub::class)->count(),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function usersCountResultSet(): void
    {
        self::assertCount(
            3,
            await(
                $this->client->repository(UserStub::class)->fetch()->toArray()->toPromise(),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function blogPostsCount(): void
    {
        self::assertSame(
            2,
            await(
                $this->client->repository(BlogPostStub::class)->count(),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function blogPostsCountResultSet(): void
    {
        self::assertCount(
            2,
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->toArray()->toPromise(),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function firstBlogPostCommentCount(): void
    {
        self::assertCount(
            2,
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getComments()->toArray()->toPromise();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function firstBlogPostAuthorId(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function firstBlogPostAuthorIdUsingLimit(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            await(
                $this->client->repository(BlogPostStub::class)->fetch(new Limit(1))->toPromise()->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
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
                    await(
                        $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                            return $blogPost->getComments()->toArray()->toPromise();
                        }),
                    ),
                ),
            ),
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function firstBlogPostNextBlogPostResolvesToBlogPost(): void
    {
        self::assertInstanceOf(
            BlogPostStub::class,
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getNextBlogPost();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function firstBlogPostPreviousBlogPostResolvesToNull(): void
    {
        self::assertNull(
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->take(1)->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getPreviousBlogPost();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function secondBlogPostCommentCount(): void
    {
        self::assertCount(
            1,
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getComments()->toArray()->toPromise();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function secondBlogPostAuthorId(): void
    {
        self::assertSame(
            '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function secondBlogPostCommentAuthorIds(): void
    {
        self::assertSame(
            ['fb175cbc-04cc-41c7-8e35-6b817ac016ca'],
            array_values(
                array_map(
                    static function (CommentStub $comment): string {
                        return $comment->getAuthor()->id();
                    },
                    await(
                        $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                            return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                        })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                            return $blogPost->getComments()->toArray()->toPromise();
                        }),
                    ),
                ),
            ),
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function secondBlogPostPreviousBlogPostAuthorId(): void
    {
        self::assertSame(
            'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getPreviousBlogPost();
                })->then(static function (BlogPostStub $blogPost): string {
                    return $blogPost->getAuthor()->id();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function secondBlogPostNextBlogPostResolvesToNull(): void
    {
        self::assertNull(
            await(
                $this->client->repository(BlogPostStub::class)->fetch()->filter(static function (BlogPostStub $blogPost): bool {
                    return $blogPost->id() === '090fa83b-5c5a-4042-9f05-58d9ab649a1a';
                })->toPromise()->then(static function (BlogPostStub $blogPost): PromiseInterface {
                    return $blogPost->getNextBlogPost();
                }),
            ),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function createUser(): void
    {
        $name = 'Commander Fuzzy paws';

        $fields = ['name' => $name];

        $user = await(
            $this->client->repository(UserStub::class)->create($fields),
        );

        self::assertSame($name, $user->getName());
        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function increaseViews(): void
    {
        sleep(3); /** @phpstan-ignore-line We're using blocking sleep here on purpose */
        self::waitUntilTheNextSecond();

        $repository = $this->client->repository(BlogPostStub::class);

        $originalBlogPost = null;
        $timestamp        = null;
        $randomContents   = bin2hex(random_bytes(13));

        $updatedBlogPost = await(
            $repository->fetch()->takeLast(1)->toPromise()->then(static function (BlogPostStub $blogPost) use (&$originalBlogPost, &$timestamp, $randomContents): BlogPostStub {
                self::waitUntilTheNextSecond();

                $originalBlogPost = $blogPost;
                $timestamp        = time();

                return $blogPost->withViews($blogPost->getViews() + 1)->withFields(['contents' => $randomContents, 'id' => 'nah', 'created' => new DateTimeImmutable(), 'modified' => new DateTimeImmutable()]);
            })->then(static function (BlogPostStub $blogPost) use ($repository): PromiseInterface {
                return $repository->update($blogPost);
            }),
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
        ], [...$this->counter->counters()]);
        self::assertNotSame($originalBlogPost->getContents(), $updatedBlogPost->getContents());
        self::assertSame($updatedBlogPost->getContents(), $randomContents);
    }

    /** @test */
    public function userSelf(): void
    {
        $repository = $this->client->repository(UserStub::class);

        $userId = null;

        $self = await($repository->fetch()->take(1)->toPromise()->then(static function (UserStub $user) use (&$userId): PromiseInterface {
            $userId = $user->id();

            return $user->getZelf();
        }));

        self::assertNotNull($userId);
        self::assertNotNull($self);
        self::assertSame($userId, $self->id());
        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    /** @test */
    public function countWithoutConstraints(): void
    {
        $repository = $this->client->repository(BlogPostStub::class);

        $count = await($repository->count());
        self::assertSame(2, $count);
    }

    /** @test */
    public function countWithConstraints(): void
    {
        $repository = $this->client->repository(BlogPostStub::class);

        $count = await($repository->count(new Where(new Where\Field('author_id', 'eq', ['fb175cbc-04cc-41c7-8e35-6b817ac016ca']))));
        self::assertSame(1, $count);
    }

    /** @test */
    public function streamLogs(): void
    {
        $repository = $this->client->repository(LogStub::class);

        $rows = await($repository->stream()->toArray()->toPromise());
        self::assertCount(256, $rows);
    }
}
