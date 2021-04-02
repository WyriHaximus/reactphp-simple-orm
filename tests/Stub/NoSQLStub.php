<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\EntityInterface;

final class NoSQLStub implements EntityInterface
{
    public function id(): string
    {
        return '';
    }
}
