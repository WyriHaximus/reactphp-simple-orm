<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

#[Table('users')]
#[InnerJoin(
    entity: UserStub::class,
    clause: [
        new Clause(
            localKey: 'name',
            foreignKey: 'name',
            foreignFunction: 'INITCAP',
        ),
    ],
    property: 'zelf',
    lazy: JoinInterface::IS_LAZY,
)]
final readonly class UserStub implements EntityInterface
{
    use WithFieldsTrait;

    /** @param PromiseInterface<UserStub> $zelf */
    public function __construct(
        public string $id,
        public string $name,
        public PromiseInterface $zelf,
    ) {
    }
}
