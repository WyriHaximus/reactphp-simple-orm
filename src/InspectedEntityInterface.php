<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

interface InspectedEntityInterface
{
    public function getClass(): string;

    public function getTable(): string;

    /**
     * @return Field[]
     */
    public function getFields(): array;

    /**
     * @return Join[]
     */
    public function getJoins(): array;
}
