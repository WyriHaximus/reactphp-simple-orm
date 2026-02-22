<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Tools;

final class IncrementingInteger
{
    private int $value = 0;

    public function getNext(): int
    {
        return $this->value++;
    }
}
