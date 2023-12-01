<?php

namespace RectorPrefix202312\React\Dns\Query;

use RectorPrefix202312\React\EventLoop\Loop;
use RectorPrefix202312\React\EventLoop\LoopInterface;
use RectorPrefix202312\React\Promise\Promise;
final class TimeoutExecutor implements ExecutorInterface
{
    private $executor;
    private $loop;
    private $timeout;
    public function __construct(ExecutorInterface $executor, $timeout, LoopInterface $loop = null)
    {
        $this->executor = $executor;
        $this->loop = $loop ?: Loop::get();
        $this->timeout = $timeout;
    }
    public function query(Query $query)
    {
        $promise = $this->executor->query($query);
        $loop = $this->loop;
        $time = $this->timeout;
        return new Promise(function ($resolve, $reject) use($loop, $time, $promise, $query) {
            $timer = null;
            $promise = $promise->then(function ($v) use(&$timer, $loop, $resolve) {
                if ($timer) {
                    $loop->cancelTimer($timer);
                }
                $timer = \false;
                $resolve($v);
            }, function ($v) use(&$timer, $loop, $reject) {
                if ($timer) {
                    $loop->cancelTimer($timer);
                }
                $timer = \false;
                $reject($v);
            });
            // promise already resolved => no need to start timer
            if ($timer === \false) {
                return;
            }
            // start timeout timer which will cancel the pending promise
            $timer = $loop->addTimer($time, function () use($time, &$promise, $reject, $query) {
                $reject(new TimeoutException('DNS query for ' . $query->describe() . ' timed out'));
                // Cancel pending query to clean up any underlying resources and references.
                // Avoid garbage references in call stack by passing pending promise by reference.
                \assert(\method_exists($promise, 'cancel'));
                $promise->cancel();
                $promise = null;
            });
        }, function () use(&$promise) {
            // Cancelling this promise will cancel the pending query, thus triggering the rejection logic above.
            // Avoid garbage references in call stack by passing pending promise by reference.
            \assert(\method_exists($promise, 'cancel'));
            $promise->cancel();
            $promise = null;
        });
    }
}
