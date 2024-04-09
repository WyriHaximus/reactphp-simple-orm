<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

final readonly class Configuration
{
    public function __construct( /** @phpstan-ignore-line */
        public string $tablePrefix = '',
    ) {
    }
}
