<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use React\Promise\PromiseInterface;
use Rx\Observable;

interface RepositoryInterface
{
    public const DEFAULT_PER_PAGE = 50;

    public function count(): PromiseInterface;

    public function page(int $page, array $where = [], array $order = [], int $perPage = self::DEFAULT_PER_PAGE): Observable;

    public function fetch(array $where = [], array $order = []): Observable;
}
