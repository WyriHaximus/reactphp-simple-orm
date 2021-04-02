<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Rx\Observable;

interface AdapterInterface
{
    public function query(ExpressionInterface $expression): Observable;

    public function engine(): EngineInterface;
}
