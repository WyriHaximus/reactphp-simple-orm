<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

final class Configuration
{
    public function __construct(private string $tablePrefix = '')
    {
    }

    public function tablePrefix(): string
    {
        return $this->tablePrefix;
    }
}
