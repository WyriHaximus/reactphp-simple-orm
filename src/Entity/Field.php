<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

/** @api */
final readonly class Field
{
    public function __construct(public string $property, public string $column, public string $type)
    {
    }
}
