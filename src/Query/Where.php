<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

final class Where implements SectionInterface
{
    /** @var array<WhereInterface> */
    private array $wheres = [];

    public function __construct(WhereInterface ...$wheres)
    {
        $this->wheres = $wheres;
    }

    /**
     * @return iterable<WhereInterface>
     */
    public function wheres(): iterable
    {
        yield from $this->wheres;
    }
}
