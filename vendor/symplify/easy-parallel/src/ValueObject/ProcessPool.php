<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Symplify\EasyParallel\ValueObject;

use RectorPrefix20220418\React\Socket\TcpServer;
use RectorPrefix20220418\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
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
    public function __construct(\RectorPrefix20220418\React\Socket\TcpServer $tcpServer)
    {
        $this->tcpServer = $tcpServer;
    }
    public function getProcess(string $identifier) : \RectorPrefix20220418\Symplify\EasyParallel\ValueObject\ParallelProcess
    {
        if (!\array_key_exists($identifier, $this->processes)) {
            throw new \RectorPrefix20220418\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException(\sprintf('Process "%s" not found.', $identifier));
        }
        return $this->processes[$identifier];
    }
    public function attachProcess(string $identifier, \RectorPrefix20220418\Symplify\EasyParallel\ValueObject\ParallelProcess $parallelProcess) : void
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
