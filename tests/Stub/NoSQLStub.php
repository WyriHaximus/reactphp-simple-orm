<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\EntityInterface;

class NoSQLStub implements EntityInterface
{
    public function getId(): void
    {
    }
}
