<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Latitude\QueryBuilder\ExpressionInterface;
use Rx\Observable;

interface ClientInterface
{
    public function getRepository(string $entity): RepositoryInterface;

    public function query(ExpressionInterface $query): Observable;
}
