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
     * @param int $page
     * @param Where[]|ExpressionWhere[] $where
     * @param mixed[] $order
     * @param int $perPage
     *
     * @return Observable
     */
    public function page(int $page, array $where = [], array $order = [], int $perPage = self::DEFAULT_PER_PAGE): Observable;

    /**
     * @param Where[]|ExpressionWhere[] $where
     * @param mixed[] $order
     * @param int $limit
     *
     * @return Observable
     */
    public function fetch(array $where = [], array $order = [], int $limit = 0): Observable;

    /**
     * @param mixed[] $fields
     *
     * @return PromiseInterface
     */
    public function create(array $fields): PromiseInterface;

    public function update(EntityInterface $entity): PromiseInterface;
}
