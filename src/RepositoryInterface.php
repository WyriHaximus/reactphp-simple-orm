<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use React\Promise\PromiseInterface;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\Query\ExpressionWhere;
use WyriHaximus\React\SimpleORM\Query\Where;

interface RepositoryInterface
{
    public const DEFAULT_PER_PAGE = 50;

    public function count(): PromiseInterface;

    /**
     * @param Where[]|ExpressionWhere[] $where
     * @param mixed[]                   $order
     */
    public function page(int $page, array $where = [], array $order = [], int $perPage = self::DEFAULT_PER_PAGE): Observable;

    /**
     * @param Where[]|ExpressionWhere[] $where
     * @param mixed[]                   $order
     */
    public function fetch(array $where = [], array $order = [], int $limit = 0): Observable;

    /**
     * @param array<string, mixed> $fields
     */
    public function create(array $fields): PromiseInterface;

    public function update(EntityInterface $entity): PromiseInterface;
}
