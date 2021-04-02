<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

interface JoinInterface
{
    public const IS_LAZY     = true;
    public const IS_NOT_LAZY = false;

    public function entity(): string;

    public function type(): string;

    public function property(): string;

    public function lazy(): bool;

    /**
     * @return Clause[]
     */
    public function clause(): array;
}
