<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Middleware;

use Latitude\QueryBuilder\ExpressionInterface;
use Throwable;
use WyriHaximus\React\SimpleORM\MiddlewareInterface;

use function hrtime;

/** @api */
final class QueryCountMiddleware implements MiddlewareInterface
{
    private const int ZERO = 0;

    private int $initiatedCount = self::ZERO;

    private int $successfulCount = self::ZERO;

    private int $erroredCount = self::ZERO;

    private int $slowCount = self::ZERO;

    private int $completedCount = self::ZERO;

    public function __construct(private readonly int $slowQueryTime)
    {
    }

    /**
     * @param callable(ExpressionInterface): iterable<array<string, mixed>> $next
     *
     * @return iterable<array<string, mixed>>
     */
    public function query(ExpressionInterface $query, callable $next): iterable
    {
        $this->initiatedCount++;
        $startTime         = hrtime()[0];
        $handledInitialRow = false;

        try {
            foreach ($next($query) as $row) {
                if (! $handledInitialRow && hrtime()[0] - $startTime > $this->slowQueryTime) {
                    $this->slowCount++;
                }

                $handledInitialRow = true;

                yield $row;
            }

            $this->successfulCount++;
            $this->completedCount++;
        } catch (Throwable $throwable) {
            $this->erroredCount++;

            if (! $handledInitialRow && hrtime()[0] - $startTime > $this->slowQueryTime) {
                $this->slowCount++;
            }

            throw $throwable;
        }
    }

    /** @return iterable<string, int> */
    public function counters(): iterable
    {
        yield 'initiated' => $this->initiatedCount;
        yield 'successful' => $this->successfulCount;
        yield 'errored' => $this->erroredCount;
        yield 'slow' => $this->slowCount;
        yield 'completed' => $this->completedCount;
    }

    public function resetCounters(): void
    {
        $this->initiatedCount  = self::ZERO;
        $this->successfulCount = self::ZERO;
        $this->erroredCount    = self::ZERO;
        $this->slowCount       = self::ZERO;
        $this->completedCount  = self::ZERO;
    }
}
