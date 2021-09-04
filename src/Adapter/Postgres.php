<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Adapter;

use Latitude\QueryBuilder\Engine\PostgresEngine;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use PgAsync\Client as PgClient;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\AdapterInterface;

use function explode;
use function implode;
use function strpos;

use const WyriHaximus\Constants\Boolean\FALSE_;
use const WyriHaximus\Constants\Numeric\ZERO;

final class Postgres implements AdapterInterface
{
    private PgClient $client;

    private EngineInterface $engine;

    public function __construct(PgClient $client)
    {
        $this->client = $client;
        $this->engine = new PostgresEngine();
    }

    public function query(ExpressionInterface $expression): Observable
    {
        $params = $expression->params($this->engine);
        $sql    = $expression->sql($this->engine);
        if (strpos($sql, '?') !== FALSE_) {
            $chunks    = explode('?', $sql);
            $sqlChunks = [];
            foreach ($chunks as $i => $chunk) {
                if ($i === ZERO) {
                    $sqlChunks[] = $chunk;
                    continue;
                }

                $sqlChunks[] = '$' . $i . $chunk;
            }

            $sql = implode('', $sqlChunks);
        }

        return $this->client->executeStatement($sql, $params);
    }

    public function engine(): EngineInterface
    {
        return $this->engine;
    }
}
