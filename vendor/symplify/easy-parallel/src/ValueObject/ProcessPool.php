<?php

declare (strict_types=1);
namespace RectorPrefix202212\Symplify\EasyParallel\ValueObject;

use RectorPrefix202212\React\Socket\TcpServer;
use RectorPrefix202212\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
/**
 * Used from https://github.com/phpstan/phpstan-src/blob/master/src/Parallel/ProcessPool.php
 */
final class ProcessPool
{
    /**
     * @var array<string, ParallelProcess>
     */
    private $processes = [];
    /**
     * @var \React\Socket\TcpServer
     */
    private $tcpServer;
    public function __construct(TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }
    public function getProcess(string $identifier) : ParallelProcess
    {
        if (!\array_key_exists($identifier, $this->processes)) {
            throw new ParallelShouldNotHappenException(\sprintf('Process "%s" not found.', $identifier));
        }
        return $this->processes[$identifier];
    }
    public function attachProcess(string $identifier, ParallelProcess $parallelProcess) : void
    {
        $this->processes[$identifier] = $parallelProcess;
    }
    public function tryQuitProcess(string $identifier) : void
    {
        if (!\array_key_exists($identifier, $this->processes)) {
            return;
        }
        $this->quitProcess($identifier);
    }
    public function quitProcess(string $identifier) : void
    {
        $parallelProcess = $this->getProcess($identifier);
        $parallelProcess->quit();
        unset($this->processes[$identifier]);
        if ($this->processes !== []) {
            return;
        }
        $this->tcpServer->close();
    }
    public function quitAll() : void
    {
        foreach (\array_keys($this->processes) as $identifier) {
            $this->quitProcess($identifier);
        }
    }
}
