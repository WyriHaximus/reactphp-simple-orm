<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\SectionInterface;
use WyriHaximus\React\SimpleORM\Query\Where;

/** @template T */
interface RepositoryInterface
{
    public const int DEFAULT_PER_PAGE = 50;

    public function count(Where|null $where = null): int;

    /** @return iterable<T> */
    public function page(int $page, Where|null $where = null, Order|null $order = null, int $perPage = self::DEFAULT_PER_PAGE): iterable;

    /** @return iterable<T> */
    public function fetch(SectionInterface ...$sections): iterable;

    /** @return T */
    public function first(SectionInterface ...$sections);

    /** @return iterable<T> */
    public function stream(SectionInterface ...$sections): iterable;

    /**
     * @param array<string, mixed> $fields
     *
     * @return T
     */
    public function create(array $fields);

    /** @return T */
    public function update(EntityInterface $entity);

    public function delete(EntityInterface $entity): null;
}
