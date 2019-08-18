<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

interface JoinInterface
{
    public const IS_LAZY = true;
    public const IS_NOT_LAZY = false;

    public function getEntity(): string;

    public function getType(): string;

    public function getProperty(): string;

    public function getLazy(): bool;

    /**
     * @return Clause[]
     */
    public function getClause(): array;
}
