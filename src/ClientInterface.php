<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use Rx\Observable;

interface ClientInterface
{
    /**
     * @param class-string<T> $entity
     *
     * @return RepositoryInterface<T>
     *
     * @template T
     */
    public function repository(string $entity): RepositoryInterface;

    /** @deprecated This function will disappear at initial release */
    public function query(ExpressionInterface $query): Observable;
}
