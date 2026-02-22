<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Deprecated;
use Latitude\QueryBuilder\ExpressionInterface;

interface ClientInterface
{
    /**
     * @param class-string<T> $entity
     *
     * @return RepositoryInterface<T>
     *
     * @template T of EntityInterface
     */
    public function repository(string $entity): RepositoryInterface;

    /** @return iterable<array<string, mixed>> */
    #[Deprecated(message: 'This function will disappear at initial release')]
    public function query(ExpressionInterface $query): iterable;
}
