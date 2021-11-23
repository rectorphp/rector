<?php

namespace RectorPrefix20211123\React\Promise;

/**
 * @deprecated 2.8.0 External usage of FulfilledPromise is deprecated, use `resolve()` instead.
 */
class FulfilledPromise implements \RectorPrefix20211123\React\Promise\ExtendedPromiseInterface, \RectorPrefix20211123\React\Promise\CancellablePromiseInterface
{
    private $value;
    public function __construct($value = null)
    {
        if ($value instanceof \RectorPrefix20211123\React\Promise\PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\\Promise\\FulfilledPromise with a promise. Use React\\Promise\\resolve($promiseOrValue) instead.');
        }
        $this->value = $value;
    }
    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     */
    public function then($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        if (null === $onFulfilled) {
            return $this;
        }
        try {
            return resolve($onFulfilled($this->value));
        } catch (\Throwable $exception) {
            return new \RectorPrefix20211123\React\Promise\RejectedPromise($exception);
        } catch (\Exception $exception) {
            return new \RectorPrefix20211123\React\Promise\RejectedPromise($exception);
        }
    }
    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     */
    public function done($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        if (null === $onFulfilled) {
            return;
        }
        $result = $onFulfilled($this->value);
        if ($result instanceof \RectorPrefix20211123\React\Promise\ExtendedPromiseInterface) {
            $result->done();
        }
    }
    /**
     * @param callable $onRejected
     */
    public function otherwise($onRejected)
    {
        return $this;
    }
    /**
     * @param callable $onFulfilledOrRejected
     */
    public function always($onFulfilledOrRejected)
    {
        return $this->then(function ($value) use($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use($value) {
                return $value;
            });
        });
    }
    /**
     * @param callable $onProgress
     */
    public function progress($onProgress)
    {
        return $this;
    }
    public function cancel()
    {
    }
}
