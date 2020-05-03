<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

interface OrderInterface
{
    public function field(): string;

    public function order(): string;
}
