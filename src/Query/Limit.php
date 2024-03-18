<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

final class Limit implements SectionInterface
{
    public function __construct(private int $limit)
    {
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
