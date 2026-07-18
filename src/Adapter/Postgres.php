<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Adapter;

use Latitude\QueryBuilder\Engine\PostgresEngine;
use Latitude\QueryBuilder\EngineInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use PgAsync\Client as PgClient;
use WyriHaximus\React\SimpleORM\AdapterInterface;

use function explode;
use function implode;
use function str_contains;
use function WyriHaximus\React\awaitObservable;

/** @api */
final readonly class Postgres implements AdapterInterface
{
    private EngineInterface $engine;

    public function __construct(private PgClient $client)
    {
        $this->engine = new PostgresEngine();
    }

    /** @return iterable<array<string, mixed>> */
    public function query(ExpressionInterface $expression): iterable
    {
        $params = $expression->params($this->engine);
        $sql    = $expression->sql($this->engine);
        if (str_contains($sql, '?')) {
            $chunks    = explode('?', $sql);
            $sqlChunks = [];
            foreach ($chunks as $i => $chunk) {
                if ($i === 0) {
                    $sqlChunks[] = $chunk;
                    continue;
                }

                $sqlChunks[] = '$' . $i . $chunk;
            }

            $sql = implode('', $sqlChunks);
        }

        /** @phpstan-ignore generator.valueType,argument.type */
        yield from awaitObservable($this->client->executeStatement($sql, $params));
    }

    public function engine(): EngineInterface
    {
        return $this->engine;
    }
}
