<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202306\Symfony\Component\Process\Exception;

use RectorPrefix202306\Symfony\Component\Process\Process;
/**
 * Exception that is thrown when a process times out.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ProcessTimedOutException extends RuntimeException
{
    public const TYPE_GENERAL = 1;
    public const TYPE_IDLE = 2;
    private $process;
    private $timeoutType;
    public function __construct(Process $process, int $timeoutType)
    {
        $this->process = $process;
        $this->timeoutType = $timeoutType;
        parent::__construct(\sprintf('The process "%s" exceeded the timeout of %s seconds.', $process->getCommandLine(), $this->getExceededTimeout()));
    }
    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
    /**
     * @return bool
     */
    public function isGeneralTimeout()
    {
        return self::TYPE_GENERAL === $this->timeoutType;
    }
    /**
     * @return bool
     */
    public function isIdleTimeout()
    {
        return self::TYPE_IDLE === $this->timeoutType;
    }
    public function getExceededTimeout() : ?float
    {
        return $this->timeoutType === self::TYPE_GENERAL ? $this->process->getTimeout() : ($this->timeoutType === self::TYPE_IDLE ? $this->process->getIdleTimeout() : null);
    }
}
