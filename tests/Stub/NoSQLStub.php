<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\EntityInterface;

final readonly class NoSQLStub implements EntityInterface
{
    public string $id;

    public function __construct()
    {
        $this->id = '';
    }
}
