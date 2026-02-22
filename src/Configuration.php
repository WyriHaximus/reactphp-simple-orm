<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

/** @api */
final readonly class Configuration
{
    /** @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue */
    public function __construct(
        public string $tablePrefix = '',
    ) {
    }
}
