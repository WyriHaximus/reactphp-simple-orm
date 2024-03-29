<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Middleware;

use Latitude\QueryBuilder\ExpressionInterface;
use React\Promise\PromiseInterface;
use Rx\Observable;
use Rx\Subject\Subject;
use Throwable;
use WyriHaximus\React\SimpleORM\MiddlewareInterface;

use function React\Promise\resolve;
use function Safe\hrtime;

final class QueryCountMiddleware implements MiddlewareInterface
{
    private const ZERO = 0;

    private int $initiatedCount = self::ZERO;

    private int $successfulCount = self::ZERO;

    private int $erroredCount = self::ZERO;

    private int $slowCount = self::ZERO;

    private int $completedCount = self::ZERO;

    public function __construct(private int $slowQueryTime)
    {
    }

    public function query(ExpressionInterface $query, callable $next): PromiseInterface
    {
        $this->initiatedCount++;

        $startTime = hrtime()[0];

        return resolve($next($query))->then(function (Observable $observable) use ($startTime): PromiseInterface {
            return resolve(Observable::defer(function () use ($observable, $startTime): Subject {
                $handledInitialRow = false;
                $subject           = new Subject();
                $observable->subscribe(
                    function (array $row) use ($subject, $startTime, &$handledInitialRow): void {
                        $subject->onNext($row);

                        if ($handledInitialRow === true) {
                            return;
                        }

                        $this->successfulCount++;

                        if (hrtime()[0] - $startTime > $this->slowQueryTime) {
                            $this->slowCount++;
                        }

                        $handledInitialRow = true;
                    },
                    function (Throwable $throwable) use ($startTime, $subject): void {
                        $this->erroredCount++;

                        if (hrtime()[0] - $startTime > $this->slowQueryTime) {
                            $this->slowCount++;
                        }

                        $subject->onError($throwable);
                    },
                    function () use ($subject, &$handledInitialRow): void {
                        $this->completedCount++;
                        $subject->onCompleted();

                        if ($handledInitialRow === true) {
                            return;
                        }

                        $this->successfulCount++;
                    },
                );

                return $subject;
            }));
        });
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
