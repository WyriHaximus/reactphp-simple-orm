<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Adapter;

use Latitude\QueryBuilder\Engine\PostgresEngine;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryInterface;
use PgAsync\Client as PgClient;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\AdapterInterface;
use const WyriHaximus\Constants\Boolean\FALSE_;
use const WyriHaximus\Constants\Numeric\ZERO;

final class Postgres implements AdapterInterface
{
    /** @var PgClient */
    private $client;

    /** @var EngineInterface */
    private $engine;

    public function __construct(PgClient $client)
    {
        $this->client = $client;
        $this->engine = new PostgresEngine();
    }

    public function query(ExpressionInterface $query): Observable
    {
        $params = $query->params($this->engine);
        $sql = $query->sql($this->engine);
        if (strpos($sql, '?') !== FALSE_) {
            $chunks = explode('?', $sql);
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
