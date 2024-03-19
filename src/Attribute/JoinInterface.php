<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

/**
 * @property string $entity
 * @property string $type
 * @property string $property
 * @property bool $lazy
 * @property array<Clause> $clause
 */
interface JoinInterface
{
    public const IS_LAZY     = true;
    public const IS_NOT_LAZY = false;
}
