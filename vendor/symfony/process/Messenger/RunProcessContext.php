<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202506\Symfony\Component\Process\Messenger;

use RectorPrefix202506\Symfony\Component\Process\Process;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunProcessContext
{
    /**
     * @readonly
     */
    public RunProcessMessage $message;
    /**
     * @readonly
     */
    public ?int $exitCode;
    /**
     * @readonly
     */
    public ?string $output;
    /**
     * @readonly
     */
    public ?string $errorOutput;
    public function __construct(RunProcessMessage $message, Process $process)
    {
        $this->message = $message;
        $this->exitCode = $process->getExitCode();
        $this->output = $process->isOutputDisabled() ? null : $process->getOutput();
        $this->errorOutput = $process->isOutputDisabled() ? null : $process->getErrorOutput();
    }
}
