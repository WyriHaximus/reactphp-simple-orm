<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Tools;

use Closure;
use React\Promise\PromiseInterface;
use Throwable;

use function call_user_func;
use function React\Promise\reject;
use function React\Promise\resolve;

/** @deprecated LazyPromise is deprecated and should not be used anymore. But temporary added it here to make migrating easier */
final class LazyPromise implements PromiseInterface
{
    private readonly Closure $factory;
    private PromiseInterface|null $promise = null;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function then(callable|null $onFulfilled = null, callable|null $onRejected = null): PromiseInterface
    {
        return $this->promise()->then($onFulfilled, $onRejected);
    }

    public function done(callable|null $onFulfilled = null, callable|null $onRejected = null): PromiseInterface
    {
        return $this->promise()->done($onFulfilled, $onRejected);
    }

    public function catch(callable $onRejected): PromiseInterface
    {
        return $this->promise()->catch($onRejected);
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->promise()->catch($onRejected);
    }

    public function finally(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->promise()->finally($onFulfilledOrRejected);
    }

    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->promise()->finally($onFulfilledOrRejected);
    }

    public function cancel(): void
    {
        $this->promise()->cancel();
    }

    /** @see Promise::settle() */
    public function promise(): PromiseInterface|null
    {
        if (! $this->promise instanceof PromiseInterface) {
            try {
                $this->promise = resolve(call_user_func($this->factory));
            } catch (Throwable $exception) {
                $this->promise = reject($exception);
            }
        }

        return $this->promise;
    }
}
