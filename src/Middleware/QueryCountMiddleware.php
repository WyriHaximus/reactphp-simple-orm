<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Middleware;

use PgAsync\Client as PgClient;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use Rx\Observable;
use Throwable;
use WyriHaximus\React\SimpleORM\MiddlewareInterface;
use function React\Promise\reject;
use function React\Promise\resolve;

final class QueryCountMiddleware implements MiddlewareInterface
{
    private const ZERO = 0;

    /** @var int */
    private $initiatedCount = self::ZERO;

    /** @var int */
    private $successfulCount = self::ZERO;

    /** @var int */
    private $erroredCount = self::ZERO;

    /** @var int */
    private $slowCount = self::ZERO;

    /** @var int */
    private $slowQueryTime;

    public function __construct(int $slowQueryTime)
    {
        $this->slowQueryTime = $slowQueryTime;
    }

    public function query(QueryBuilder $query, callable $next): PromiseInterface
    {
        $this->initiatedCount++;

        $startTime = hrtime()[0];

        return resolve($next($query))->then(function (Observable $observable) use ($startTime): PromiseInterface {
            $this->successfulCount++;

            if (hrtime()[0] - $startTime > $this->slowQueryTime) {
                $this->slowCount++;
            }

            return resolve($observable);
        }, function (Throwable $throwable) use ($startTime): PromiseInterface {
            $this->erroredCount++;

            if (hrtime()[0] - $startTime > $this->slowQueryTime) {
                $this->slowCount++;
            }

            return reject($throwable);
        });
    }

    public function getCounters(): iterable
    {
        yield 'initiated' => $this->initiatedCount;
        yield 'successful' => $this->successfulCount;
        yield 'errored' => $this->erroredCount;
        yield 'slow' => $this->slowCount;
    }

    public function resetCounters(): void
    {
        $this->initiatedCount = self::ZERO;
        $this->successfulCount = self::ZERO;
        $this->erroredCount = self::ZERO;
        $this->slowCount = self::ZERO;
    }
}
