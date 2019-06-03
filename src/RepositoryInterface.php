<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use React\Promise\PromiseInterface;
use Rx\Observable;

interface RepositoryInterface
{
    public function count(): PromiseInterface;
    public function page(int $page, array $where = [], array $order = [], int $perPage = 50): Observable;
    public function fetch(array $where = []): Observable;
}
