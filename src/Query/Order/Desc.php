<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Order;

use WyriHaximus\React\SimpleORM\Query\OrderInterface;

final class Desc implements OrderInterface
{
    public function __construct(private string $field)
    {
    }

    public function field(): string
    {
        return $this->field;
    }

    public function order(): string
    {
        return 'DESC';
    }
}
