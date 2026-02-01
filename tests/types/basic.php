<?php

declare(strict_types=1);

use PgAsync\Client as PgClient;
use React\EventLoop\Loop;
use WyriHaximus\React\SimpleORM\Adapter\Postgres;
use WyriHaximus\React\SimpleORM\Client;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\Middleware\QueryCountMiddleware;
use WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub;

use function PHPStan\Testing\assertType;

$client     = Client::create(
    new Postgres(
        new PgClient(
            [
                'host'     => 'localhost',
                'port'     => 55432,
                'user'     => 'postgres',
                'password' => 'postgres',
                'database' => 'postgres',
            ],
            Loop::get(),
        ),
    ),
    new Configuration(''),
    new QueryCountMiddleware(1),
);
$repository = $client->repository(NoSQLStub::class);

assertType('WyriHaximus\React\SimpleORM\RepositoryInterface<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository);
assertType('int', $repository->count());
assertType('iterable<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository->fetch());
assertType('iterable<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository->page(1));
assertType('iterable<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository->stream());
