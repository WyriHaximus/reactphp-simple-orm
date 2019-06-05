<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PgAsync\Client as PgClient;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
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
                    'user'     => getenv('PHINX_DB_USER'),
                    'password' => getenv('PHINX_DB_PASSWORD'),
                    'database' => getenv('PHINX_DB_DATABASE'),
                ],
                $this->loop
            )
        );
    }

    /**
     * @test
     */
    public function usersCount()
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
    public function usersCountResultSet()
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
    public function blogPostsCount()
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
    public function blogPostsCountResultSet()
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
    public function firstBlogPostCommentCount()
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
}
