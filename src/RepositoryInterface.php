<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use React\Promise\PromiseInterface;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\SectionInterface;
use WyriHaximus\React\SimpleORM\Query\Where;

/** @template T */
interface RepositoryInterface
{
    public const DEFAULT_PER_PAGE = 50;

    /**
     * @return PromiseInterface<int>
     *
     * @phpstan-ignore-next-line
     */
    public function count(Where|null $where = null): PromiseInterface;

    /**
     * @return Observable<T>
     *
     * @phpstan-ignore-next-line
     */
    public function page(int $page, Where|null $where = null, Order|null $order = null, int $perPage = self::DEFAULT_PER_PAGE): Observable;

    /** @return Observable<T> */
    public function fetch(SectionInterface ...$sections): Observable;

    /** @return Observable<T> */
    public function stream(SectionInterface ...$sections): Observable;

    /**
     * @param array<string, mixed> $fields
     *
     * @return PromiseInterface<T>
     */
    public function create(array $fields): PromiseInterface;

    /** @return PromiseInterface<T> */
    public function update(EntityInterface $entity): PromiseInterface;

    /** @return PromiseInterface<null> */
    public function delete(EntityInterface $entity): PromiseInterface;
}
