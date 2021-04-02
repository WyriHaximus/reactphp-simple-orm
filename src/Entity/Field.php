<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

final class Field
{
    private string $name;

    private string $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
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
