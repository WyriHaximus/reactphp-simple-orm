<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use PgAsync\Client as PgClient;
use Plasma\SQL\Grammar\PostgreSQL;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;

final class Client
{
    /** @var PgClient */
    private $client;

    /** @var string[] */
    private $entityTableMap;

    public function __construct(PgClient $client, array $entityTableMap = [])
    {
        $this->client = $client;
        $this->entityTableMap = $entityTableMap;
    }

    public function fetch(QueryBuilder $query): Observable
    {
        $query = $query->withGrammar(new PostgreSQL());

        return $this->client->executeStatement($query->getQuery(), $query->getParameters());
    }
}
