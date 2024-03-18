<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

/**
 * @Table("users")
 * @InnerJoin(
        entity=UserStub::class,
        clause={
            @Clause(
                local_key="name",
                foreign_key="name",
                foreign_function="INITCAP",
            )
        },
        property="zelf",
        lazy=true
 * )
 */
final class UserStub implements EntityInterface
{
    use WithFieldsTrait;

    protected string $id;

    protected string $name;

    /**
     * @var PromiseInterface<UserStub>
     */
    protected PromiseInterface $zelf;

    public function id(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return PromiseInterface<UserStub>
     */
    public function getZelf(): PromiseInterface
    {
        return $this->zelf;
    }
}
