<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

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
class UserStub implements EntityInterface
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var PromiseInterface */
    protected $zelf;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getZelf(): PromiseInterface
    {
        return $this->zelf;
    }
}
