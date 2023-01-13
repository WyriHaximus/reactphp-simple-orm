<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

final class Configuration
{
    private string $tablePrefix = '';

    public function __construct(string $tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    public function tablePrefix(): string
    {
        return $this->tablePrefix;
    }
}
