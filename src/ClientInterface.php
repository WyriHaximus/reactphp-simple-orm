<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\QueryBuilder;
use Rx\Observable;

interface ClientInterface
{
    public function getRepository(string $entity): Repository;

    public function fetch(QueryBuilder $query): Observable;
}
