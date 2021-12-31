<?php

namespace RectorPrefix20211231\React\Promise;

/**
 * @deprecated 2.8.0 External usage of FulfilledPromise is deprecated, use `resolve()` instead.
 */
class FulfilledPromise implements \RectorPrefix20211231\React\Promise\ExtendedPromiseInterface, \RectorPrefix20211231\React\Promise\CancellablePromiseInterface
{
    private $value;
    public function __construct($value = null)
    {
        if ($value instanceof \RectorPrefix20211231\React\Promise\PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\\Promise\\FulfilledPromise with a promise. Use React\\Promise\\resolve($promiseOrValue) instead.');
        }
        $this->value = $value;
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }
        try {
            return resolve($onFulfilled($this->value));
        } catch (\Throwable $exception) {
            return new \RectorPrefix20211231\React\Promise\RejectedPromise($exception);
        } catch (\Exception $exception) {
            return new \RectorPrefix20211231\React\Promise\RejectedPromise($exception);
        }
    }
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) {
            return;
        }
        $result = $onFulfilled($this->value);
        if ($result instanceof \RectorPrefix20211231\React\Promise\ExtendedPromiseInterface) {
            $result->done();
        }
    }
    public function otherwise(callable $onRejected)
    {
        return $this;
    }
    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(function ($value) use($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use($value) {
                return $value;
            });
        });
    }
    public function progress(callable $onProgress)
    {
        return $this;
    }
    public function cancel()
    {
    }
}
