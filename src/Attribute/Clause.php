<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

final readonly class Clause
{
    public function __construct(
        public string $localKey,
        public string $foreignKey,
        public string|null $localCast = null,
        public string|null $localFunction = null,
        public string|null $foreignCast = null,
        public string|null $foreignFunction = null,
    ) {
    }
}
