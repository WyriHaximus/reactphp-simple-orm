<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

final class Limit implements SectionInterface
{
    private int $limit;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
