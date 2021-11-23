<?php

namespace RectorPrefix20211123\React\Dns\Query;

use RectorPrefix20211123\React\EventLoop\Loop;
use RectorPrefix20211123\React\EventLoop\LoopInterface;
use RectorPrefix20211123\React\Promise\Timer;
final class TimeoutExecutor implements \RectorPrefix20211123\React\Dns\Query\ExecutorInterface
{
    private $executor;
    private $loop;
    private $timeout;
    public function __construct(\RectorPrefix20211123\React\Dns\Query\ExecutorInterface $executor, $timeout, \RectorPrefix20211123\React\EventLoop\LoopInterface $loop = null)
    {
        $this->executor = $executor;
        $this->loop = $loop ?: \RectorPrefix20211123\React\EventLoop\Loop::get();
        $this->timeout = $timeout;
    }
    /**
     * @param \React\Dns\Query\Query $query
     */
    public function query($query)
    {
        return \RectorPrefix20211123\React\Promise\Timer\timeout($this->executor->query($query), $this->timeout, $this->loop)->then(null, function ($e) use($query) {
            if ($e instanceof \RectorPrefix20211123\React\Promise\Timer\TimeoutException) {
                $e = new \RectorPrefix20211123\React\Dns\Query\TimeoutException(\sprintf("DNS query for %s timed out", $query->describe()), 0, $e);
            }
            throw $e;
        });
    }
}
