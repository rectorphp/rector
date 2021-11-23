<?php

namespace RectorPrefix20211123\React\Promise;

/**
 * @deprecated 2.8.0 LazyPromise is deprecated and should not be used anymore.
 */
class LazyPromise implements \RectorPrefix20211123\React\Promise\ExtendedPromiseInterface, \RectorPrefix20211123\React\Promise\CancellablePromiseInterface
{
    private $factory;
    private $promise;
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }
    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     */
    public function then($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        return $this->promise()->then($onFulfilled, $onRejected, $onProgress);
    }
    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     */
    public function done($onFulfilled = null, $onRejected = null, $onProgress = null)
    {
        return $this->promise()->done($onFulfilled, $onRejected, $onProgress);
    }
    /**
     * @param callable $onRejected
     */
    public function otherwise($onRejected)
    {
        return $this->promise()->otherwise($onRejected);
    }
    /**
     * @param callable $onFulfilledOrRejected
     */
    public function always($onFulfilledOrRejected)
    {
        return $this->promise()->always($onFulfilledOrRejected);
    }
    /**
     * @param callable $onProgress
     */
    public function progress($onProgress)
    {
        return $this->promise()->progress($onProgress);
    }
    public function cancel()
    {
        return $this->promise()->cancel();
    }
    /**
     * @internal
     * @see Promise::settle()
     */
    public function promise()
    {
        if (null === $this->promise) {
            try {
                $this->promise = resolve(\call_user_func($this->factory));
            } catch (\Throwable $exception) {
                $this->promise = new \RectorPrefix20211123\React\Promise\RejectedPromise($exception);
            } catch (\Exception $exception) {
                $this->promise = new \RectorPrefix20211123\React\Promise\RejectedPromise($exception);
            }
        }
        return $this->promise;
    }
}
