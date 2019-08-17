<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

interface JoinInterface
{
    public function getEntity(): string;

    public function getType(): string;

    public function getProperty(): string;

    /**
     * @return Clause[]
     */
    public function getClause(): array;
}
