<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PgAsync\Client as PgClient;
use PgAsync\Connection;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Safe\DateTimeImmutable;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedTestContainer;
use Testcontainers\Modules\PostgresContainer;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\DevApp\React\SimpleORM\BlogPostStub;
use WyriHaximus\DevApp\React\SimpleORM\CommentStub;
use WyriHaximus\DevApp\React\SimpleORM\LogStub;
use WyriHaximus\DevApp\React\SimpleORM\UserStub;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use WyriHaximus\React\SimpleORM\Query\Limit;
use WyriHaximus\React\SimpleORM\Query\Where;
use WyriHaximus\React\SimpleORM\RepositoryInterface;

use function array_filter;
use function array_map;
use function array_values;
use function bin2hex;
use function current;
use function dirname;
use function is_string;
use function random_bytes;
use function React\Async\await;
use function React\Promise\Timer\sleep;
use function str_contains;
use function time;
use function usleep;

final class FunctionalTest extends AsyncTestCase
{
    private StartedTestContainer|null $testContainer = null;
    private ClientInterface|null $client             = null;

    private QueryCountMiddleware $counter;

    #[Before]
    protected function beforeClass(): void
    {
        $containerName = 'reactphp-simple-orm-test-' . bin2hex(random_bytes(13));

        $this->testContainer = new PostgresContainer()
            ->withPostgresDatabase('postgres')
            ->withPostgresUser('postgres')
            ->withPostgresPassword('postgres')
            ->withName($containerName)
            ->start();

        do {
            $ip = current($this->testContainer->getNetworkNames());
        } while (! is_string($ip));

        $migrationsContainer = new GenericContainer('flyway/flyway')
            ->withCommand(['migrate'])
            ->withEnvironment([
                'FLYWAY_LOCATIONS' => 'filesystem:/flyway/migrations',
                'FLYWAY_URL' => 'jdbc:postgresql://' . $this->testContainer->getIpAddress($ip) . ':5432/postgres?searchpath=postgres&currentSchema=postgres&stringtype=unspecified',
                'FLYWAY_USER' => 'postgres',
                'FLYWAY_PASSWORD' => 'postgres',
            ])
            ->withMount(dirname(__DIR__) . '/etc/db', '/flyway/migrations')
            ->withMount('/var/run/docker.sock', '/var/run/docker.sock')
            ->start();

        $logsBuffer = $migrationsContainer->logs();
        while (! str_contains($logsBuffer, ' (execution time ')) {
            /**
             * It's fine, this is during setup
             *
             * @phpstan-ignore wyrihaximus.reactphp.blocking.function.usleep
             */
            usleep(100);
            $logsBuffer .= $migrationsContainer->logs();
        }

        $migrationsContainer->stop();

        $this->counter = new QueryCountMiddleware(1);

        $this->client = Client::create(
            new Postgres(
                new PgClient(
                    [
                        'host'     => $this->testContainer->getIpAddress($ip),
                        'port'     => 5432,
                        'user'     => 'postgres',
                        'password' => 'postgres',
                        'database' => 'postgres',
                        'auto_disconnect' => true,
                        'max_connections' => 1,
                        'tls' => Connection::TLS_MODE_DISABLE,
                    ],
                ),
            ),
            new Configuration(''),
            $this->counter,
        );
    }

    #[After]
    public function shutdownContainer(): void
    {
        $this->testContainer?->stop();
        sleep(3);
    }

    #[Test]
    public function usersCount(): void
    {
        self::assertSame(
            3,
            $this->client?->repository(UserStub::class)->count(),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function usersCountResultSet(): void
    {
        self::assertCount(
            3,
            [...$this->client?->repository(UserStub::class)->fetch() ?? []],
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function blogPostsCount(): void
    {
        self::assertSame(
            2,
            $this->client?->repository(BlogPostStub::class)->count(),
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function blogPostsCountResultSet(): void
    {
        self::assertCount(
            2,
            [...$this->client?->repository(BlogPostStub::class)->fetch() ?? []],
        );

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function firstBlogPostCommentCount(): void
    {
        foreach ($this->client?->repository(BlogPostStub::class)->fetch() ?? [] as $blogPost) {
            if ($blogPost->id !== '53ab5832-9a90-4e6e-988b-06b8b5fed763') {
                continue;
            }

            self::assertCount(2, [...$blogPost->comments]);
            break;
        }

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function firstBlogPostAuthorId(): void
    {
        $first = false;
        foreach ($this->client?->repository(BlogPostStub::class)->fetch() ?? [] as $blogPost) {
            if ($first) {
                continue;
            }

            $first = true;
            self::assertSame('fb175cbc-04cc-41c7-8e35-6b817ac016ca', $blogPost->author->id);
        }

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function firstBlogPostAuthorIdUsingLimit(): void
    {
        foreach ($this->client?->repository(BlogPostStub::class)->fetch(new Limit(1)) ?? [] as $blogPost) {
            self::assertSame('fb175cbc-04cc-41c7-8e35-6b817ac016ca', $blogPost->author->id);
        }

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function firstBlogPostCommentAuthorIds(): void
    {
        foreach ($this->client?->repository(BlogPostStub::class)->fetch() ?? [] as $blogPost) {
            self::assertSame(
                [
                    '2fa0d077-d374-4409-b1ef-9687c6729158',
                    '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
                ],
                array_values(
                    array_map(
                        static fn (CommentStub $comment): string => $comment->author->id,
                        [...$blogPost->comments],
                    ),
                ),
            );
            break;
        }

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function firstBlogPostNextBlogPostResolvesToBlogPost(): void
    {
        foreach ($this->client?->repository(BlogPostStub::class)->fetch() ?? [] as $blogPost) {
            self::assertInstanceOf(BlogPostStub::class, $blogPost->nextBlogPost);
            break;
        }

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function firstBlogPostPreviousBlogPostResolvesToNull(): void
    {
        foreach ($this->client?->repository(BlogPostStub::class)->fetch() ?? [] as $blogPost) {
            self::assertNull($blogPost->previousBlogPost);
            break;
        }

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function secondBlogPostCommentCount(): void
    {
        foreach (
            array_filter(
                [...$this->client?->repository(BlogPostStub::class)->fetch() ?? []],
                static fn (BlogPostStub $blogPost): bool => $blogPost->id === '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
            ) as $blogPost
        ) {
            self::assertCount(
                1,
                [...$blogPost->comments],
            );
        }

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function secondBlogPostAuthorId(): void
    {
        foreach (
            array_filter(
                [...$this->client?->repository(BlogPostStub::class)->fetch() ?? []],
                static fn (BlogPostStub $blogPost): bool => $blogPost->id === '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
            ) as $blogPost
        ) {
            self::assertSame(
                '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
                $blogPost->author->id,
            );
        }

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function secondBlogPostCommentAuthorIds(): void
    {
        foreach (
            array_filter(
                [...$this->client?->repository(BlogPostStub::class)->fetch() ?? []],
                static fn (BlogPostStub $blogPost): bool => $blogPost->id === '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
            ) as $blogPost
        ) {
            self::assertSame(
                ['fb175cbc-04cc-41c7-8e35-6b817ac016ca'],
                array_values(
                    array_map(
                        static fn (CommentStub $comment): string => $comment->author->id,
                        [...$blogPost->comments],
                    ),
                ),
            );
            break;
        }

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function secondBlogPostPreviousBlogPostAuthorId(): void
    {
        foreach (
            array_filter(
                [...$this->client?->repository(BlogPostStub::class)->fetch() ?? []],
                static fn (BlogPostStub $blogPost): bool => $blogPost->id === '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
            ) as $blogPost
        ) {
            self::assertInstanceOf(BlogPostStub::class, $blogPost->previousBlogPost);

            self::assertSame(
                'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
                $blogPost->previousBlogPost->author->id,
            );
        }

        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function secondBlogPostNextBlogPostResolvesToNull(): void
    {
        foreach (
            array_filter(
                [...$this->client?->repository(BlogPostStub::class)->fetch() ?? []],
                static fn (BlogPostStub $blogPost): bool => $blogPost->id === '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
            ) as $blogPost
        ) {
            self::assertNull($blogPost->nextBlogPost);
        }

        self::assertSame([
            'initiated' => 1,
            'successful' => 1,
            'errored' => 0,
            'slow' => 0,
            'completed' => 1,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function createUser(): void
    {
        $name = 'Commander Fuzzy paws';

        $fields = ['name' => $name];

        $user = $this->client?->repository(UserStub::class)->create($fields);
        self::assertInstanceOf(UserStub::class, $user);

        self::assertSame($name, $user->name);
        self::assertSame([
            'initiated' => 2,
            'successful' => 2,
            'errored' => 0,
            'slow' => 0,
            'completed' => 2,
        ], [...$this->counter->counters()]);
    }

    #[Test]
    public function increaseViews(): void
    {
        await(sleep(3));
        self::waitUntilTheNextSecond();

        $repository = $this->client?->repository(BlogPostStub::class);
        self::assertInstanceOf(RepositoryInterface::class, $repository);

        $blogPost         = null;
        $originalBlogPost = null;
        $randomContents   = bin2hex(random_bytes(13));

        /** @phpstan-ignore foreach.valueOverwrite */
        foreach (
            $repository->first(
                new Where(
                    new Where\Field(
                        'id',
                        'eq',
                        ['090fa83b-5c5a-4042-9f05-58d9ab649a1a'],
                    ),
                ),
            ) as $blogPost
        ) {
            $originalBlogPost = $blogPost;
        }

        self::assertNotNull($blogPost);
        self::waitUntilTheNextSecond();

        $timestamp       = time();
        $updatedBlogPost = $repository->update(
            $blogPost->withFields(['views' => $blogPost->views + 1, 'contents' => $randomContents, 'id' => 'nah', 'created' => new DateTimeImmutable(), 'modified' => new DateTimeImmutable()]),
        );

        self::assertInstanceOf(BlogPostStub::class, $originalBlogPost);

        self::assertSame(167, $updatedBlogPost->views);
        self::assertSame($originalBlogPost->id, $updatedBlogPost->id);
        self::assertSame($originalBlogPost->created->format('U'), $updatedBlogPost->created->format('U'));
        self::assertGreaterThan($originalBlogPost->modified, $updatedBlogPost->modified);
        self::assertSame($timestamp, (int) $updatedBlogPost->modified->format('U'));
        self::assertSame([
            'initiated' => 3,
            'successful' => 3,
            'errored' => 0,
            'slow' => 0,
            'completed' => 3,
        ], [...$this->counter->counters()]);
        self::assertNotSame($originalBlogPost->contents, $updatedBlogPost->contents);
        self::assertSame($updatedBlogPost->contents, $randomContents);
    }

    #[Test]
    public function countWithoutConstraints(): void
    {
        $repository = $this->client?->repository(BlogPostStub::class);
        self::assertInstanceOf(RepositoryInterface::class, $repository);

        $count = $repository->count();
        self::assertSame(2, $count);
    }

    #[Test]
    public function countWithConstraints(): void
    {
        $repository = $this->client?->repository(BlogPostStub::class);
        self::assertInstanceOf(RepositoryInterface::class, $repository);

        $count = $repository->count(new Where(new Where\Field('author_id', 'eq', ['fb175cbc-04cc-41c7-8e35-6b817ac016ca'])));
        self::assertSame(1, $count);
    }

    #[Test]
    public function streamLogs(): void
    {
        $repository = $this->client?->repository(LogStub::class);
        self::assertInstanceOf(RepositoryInterface::class, $repository);

        self::assertCount(256, [...$repository->stream()]);
    }
}
