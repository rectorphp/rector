<?php

namespace RectorPrefix202407\React\Socket;

use RectorPrefix202407\React\EventLoop\Loop;
use RectorPrefix202407\React\EventLoop\LoopInterface;
use RectorPrefix202407\React\Promise\Promise;
final class TimeoutConnector implements ConnectorInterface
{
    private $connector;
    private $timeout;
    private $loop;
    public function __construct(ConnectorInterface $connector, $timeout, LoopInterface $loop = null)
    {
        $this->connector = $connector;
        $this->timeout = $timeout;
        $this->loop = $loop ?: Loop::get();
    }
    public function connect($uri)
    {
        $promise = $this->connector->connect($uri);
        $loop = $this->loop;
        $time = $this->timeout;
        return new Promise(function ($resolve, $reject) use($loop, $time, $promise, $uri) {
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
            $timer = $loop->addTimer($time, function () use($time, &$promise, $reject, $uri) {
                $reject(new \RuntimeException('Connection to ' . $uri . ' timed out after ' . $time . ' seconds (ETIMEDOUT)', \defined('SOCKET_ETIMEDOUT') ? \SOCKET_ETIMEDOUT : 110));
                // Cancel pending connection to clean up any underlying resources and references.
                // Avoid garbage references in call stack by passing pending promise by reference.
                \assert(\method_exists($promise, 'cancel'));
                $promise->cancel();
                $promise = null;
            });
        }, function () use(&$promise) {
            // Cancelling this promise will cancel the pending connection, thus triggering the rejection logic above.
            // Avoid garbage references in call stack by passing pending promise by reference.
            \assert(\method_exists($promise, 'cancel'));
            $promise->cancel();
            $promise = null;
        });
    }
}
