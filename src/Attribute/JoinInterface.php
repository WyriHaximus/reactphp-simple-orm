<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

use WyriHaximus\React\SimpleORM\Entity\JointType;

/**
 * @property JointType $type
 * @property bool $lazy
 * @property array<Clause> $clause
 */
interface JoinInterface
{
    public const true IS_LAZY      = true;
    public const false IS_NOT_LAZY = false;
}
