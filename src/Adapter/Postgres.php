<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Adapter;

use PgAsync\Client as PgClient;
use Plasma\SQL\Grammar\PostgreSQL;
use Plasma\SQL\GrammarInterface;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\AdapterInterface;

final class Postgres implements AdapterInterface
{
    /** @var PgClient */
    private $client;

    public function __construct(PgClient $client)
    {
        $this->client = $client;
    }

    public function query(QueryBuilder $query): Observable
    {
        return $this->client->executeStatement($query->getQuery(), $query->getParameters());
    }

    public function getGrammar(): GrammarInterface
    {
        return new PostgreSQL();
    }
}
