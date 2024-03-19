<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

final class Field
{
    public function __construct(private string $name, private string $type)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }
}
