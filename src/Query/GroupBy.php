<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

final class GroupBy implements SectionInterface
{
    /** @var array<string> */
    private array $columns = [];

    public function __construct(string ...$columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return iterable<string>
     */
    public function columns(): iterable
    {
        yield from $this->columns;
    }
}
