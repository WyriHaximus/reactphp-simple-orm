<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

/**
 * @Table("users")
 */
class UserStub implements EntityInterface
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
