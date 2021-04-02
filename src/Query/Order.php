<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

final class Order implements SectionInterface
{
    /** @var array<OrderInterface> */
    private array $orders = [];

    public function __construct(OrderInterface ...$orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return iterable<OrderInterface>
     */
    public function orders(): iterable
    {
        yield from $this->orders;
    }
}
