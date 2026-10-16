<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Fixtures;

use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

#[Table('runtime_inspect_users')]
final readonly class RuntimeInspectUserStub implements EntityInterface
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
