<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

/** @template T of EntityInterface */
interface InspectedEntityInterface
{
    /** @return class-string<T> */
    public function class(): string;

    public function table(): string;

    /** @return array<Field> */
    public function fields(): array;

    /** @return array<Join> */
    public function joins(): array;
}
