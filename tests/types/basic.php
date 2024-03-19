<?php

declare(strict_types = 1);

use function PHPStan\Testing\assertType;

/** @phpstan-ignore-next-line */
$client = \WyriHaximus\React\SimpleORM\Client::create();
$repository = $client->repository(\WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub::class);

assertType('WyriHaximus\React\SimpleORM\RepositoryInterface<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository);
assertType('React\Promise\PromiseInterface<int>', $repository->count());
assertType('Rx\Observable<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository->fetch());
assertType('Rx\Observable<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository->page(1));
assertType('Rx\Observable<WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub>', $repository->stream());
