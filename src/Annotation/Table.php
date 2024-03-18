<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

use function current;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Table
{
    private string $table;

    /** @param string[] $table */
    public function __construct(array $table)
    {
        $this->table = current($table); /** @phpstan-ignore-line */
    }

    public function table(): string
    {
        return $this->table;
    }
}
