<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;

interface AdapterInterface
{
    /** @return iterable<array<string, mixed>> */
    public function query(ExpressionInterface $expression): iterable;

    public function engine(): EngineInterface;
}
