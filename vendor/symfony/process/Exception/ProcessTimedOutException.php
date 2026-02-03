<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202602\Symfony\Component\Process\Exception;

use RectorPrefix202602\Symfony\Component\Process\Process;
/**
 * Exception that is thrown when a process times out.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ProcessTimedOutException extends RuntimeException
{
    private Process $process;
    private int $timeoutType;
    public const TYPE_GENERAL = 1;
    public const TYPE_IDLE = 2;
    public function __construct(Process $process, int $timeoutType)
    {
        $this->process = $process;
        $this->timeoutType = $timeoutType;
        parent::__construct(\sprintf('The process "%s" exceeded the timeout of %s seconds.', $process->getCommandLine(), $this->getExceededTimeout()));
    }
    public function getProcess(): Process
    {
        return $this->process;
    }
    public function isGeneralTimeout(): bool
    {
        return self::TYPE_GENERAL === $this->timeoutType;
    }
    public function isIdleTimeout(): bool
    {
        return self::TYPE_IDLE === $this->timeoutType;
    }
    public function getExceededTimeout(): ?float
    {
        switch ($this->timeoutType) {
            case self::TYPE_GENERAL:
                return $this->process->getTimeout();
            case self::TYPE_IDLE:
                return $this->process->getIdleTimeout();
            default:
                throw new \LogicException(\sprintf('Unknown timeout type "%d".', $this->timeoutType));
        }
    }
}
