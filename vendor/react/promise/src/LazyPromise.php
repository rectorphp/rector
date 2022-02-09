<?php

namespace RectorPrefix20220209\React\Promise;

/**
 * @deprecated 2.8.0 LazyPromise is deprecated and should not be used anymore.
 */
class LazyPromise implements \RectorPrefix20220209\React\Promise\ExtendedPromiseInterface, \RectorPrefix20220209\React\Promise\CancellablePromiseInterface
{
    private $factory;
    private $promise;
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return $this->promise()->then($onFulfilled, $onRejected, $onProgress);
    }
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return $this->promise()->done($onFulfilled, $onRejected, $onProgress);
    }
    public function otherwise(callable $onRejected)
    {
        return $this->promise()->otherwise($onRejected);
    }
    public function always(callable $onFulfilledOrRejected)
    {
        return $this->promise()->always($onFulfilledOrRejected);
    }
    public function progress(callable $onProgress)
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
                $this->promise = new \RectorPrefix20220209\React\Promise\RejectedPromise($exception);
            } catch (\Exception $exception) {
                $this->promise = new \RectorPrefix20220209\React\Promise\RejectedPromise($exception);
            }
        }
        return $this->promise;
    }
}
