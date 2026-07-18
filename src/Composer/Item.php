<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Composer;

use JsonSerializable;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;
use WyriHaximus\React\SimpleORM\EntityInterface;

final readonly class Item implements ItemContract, JsonSerializable
{
    /** @param class-string<EntityInterface> $class */
    public function __construct(
        public string $class,
    ) {
    }

    /** @return array{class: class-string<EntityInterface>} */
    public function jsonSerialize(): array
    {
        return [
            'class' => $this->class,
        ];
    }
}
