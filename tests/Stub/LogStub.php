<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

#[Table('logs')]
final readonly class LogStub implements EntityInterface
{
    use WithFieldsTrait;

    public function __construct(
        public string $id,
        public string $message,
    ) {
    }
}
