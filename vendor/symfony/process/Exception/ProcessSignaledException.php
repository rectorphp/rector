<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220121\Symfony\Component\Process\Exception;

use RectorPrefix20220121\Symfony\Component\Process\Process;
/**
 * Exception that is thrown when a process has been signaled.
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class ProcessSignaledException extends \RectorPrefix20220121\Symfony\Component\Process\Exception\RuntimeException
{
    private $process;
    public function __construct(\RectorPrefix20220121\Symfony\Component\Process\Process $process)
    {
        $this->process = $process;
        parent::__construct(\sprintf('The process has been signaled with signal "%s".', $process->getTermSignal()));
    }
    public function getProcess() : \RectorPrefix20220121\Symfony\Component\Process\Process
    {
        return $this->process;
    }
    public function getSignal() : int
    {
        return $this->getProcess()->getTermSignal();
    }
}
