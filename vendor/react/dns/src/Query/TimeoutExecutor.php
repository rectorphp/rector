<?php

namespace RectorPrefix20220418\React\Dns\Query;

use RectorPrefix20220418\React\EventLoop\Loop;
use RectorPrefix20220418\React\EventLoop\LoopInterface;
use RectorPrefix20220418\React\Promise\Timer;
final class TimeoutExecutor implements \RectorPrefix20220418\React\Dns\Query\ExecutorInterface
{
    private $executor;
    private $loop;
    private $timeout;
    public function __construct(\RectorPrefix20220418\React\Dns\Query\ExecutorInterface $executor, $timeout, \RectorPrefix20220418\React\EventLoop\LoopInterface $loop = null)
    {
        $this->executor = $executor;
        $this->loop = $loop ?: \RectorPrefix20220418\React\EventLoop\Loop::get();
        $this->timeout = $timeout;
    }
    public function query(\RectorPrefix20220418\React\Dns\Query\Query $query)
    {
        return \RectorPrefix20220418\React\Promise\Timer\timeout($this->executor->query($query), $this->timeout, $this->loop)->then(null, function ($e) use($query) {
            if ($e instanceof \RectorPrefix20220418\React\Promise\Timer\TimeoutException) {
                $e = new \RectorPrefix20220418\React\Dns\Query\TimeoutException(\sprintf("DNS query for %s timed out", $query->describe()), 0, $e);
            }
            throw $e;
        });
    }
}
