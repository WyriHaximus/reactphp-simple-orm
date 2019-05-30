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

    /** @var Repository[] */
    private $repositories = [];

    public function __construct(PgClient $client)
    {
        $this->client = $client;
    }

    public function getRepository(string $entity): Repository
    {
        if (!isset($this->repositories[$entity])) {
            $this->repositories[$entity] = new Repository($this, $entity);
        }

        return $this->repositories[$entity];
    }

    public function fetch(QueryBuilder $query): Observable
    {
        $query = $query->withGrammar(new PostgreSQL());

        return $this->client->executeStatement($query->getQuery(), $query->getParameters());
    }
}
