<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use React\Promise\PromiseInterface;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\Where;

interface RepositoryInterface
{
    public const DEFAULT_PER_PAGE = 50;

    /** @phpstan-ignore-next-line */
    public function count(?Where $where = null): PromiseInterface;

    /** @phpstan-ignore-next-line */
    public function page(int $page, ?Where $where = null, ?Order $order = null, int $perPage = self::DEFAULT_PER_PAGE): Observable;

    /** @phpstan-ignore-next-line */
    public function fetch(?Where $where = null, ?Order $order = null, int $limit = 0): Observable;

    /**
     * @param array<string, mixed> $fields
     */
    public function create(array $fields): PromiseInterface;

    public function update(EntityInterface $entity): PromiseInterface;

    public function delete(EntityInterface $entity): PromiseInterface;
}
