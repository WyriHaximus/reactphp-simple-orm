<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Order;

use WyriHaximus\React\SimpleORM\Query\OrderInterface;

final class Asc implements OrderInterface
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function field(): string
    {
        return $this->field;
    }

    public function order(): string
    {
        return 'ASC';
    }
}
