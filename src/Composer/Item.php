<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Composer;

use JsonSerializable;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;

final readonly class Item implements ItemContract, JsonSerializable
{
    /** @param class-string $class */
    public function __construct(
        public string $class,
    ) {
    }

    /** @return array{class: class-string} */
    public function jsonSerialize(): array
    {
        return [
            'class' => $this->class,
        ];
    }
}
