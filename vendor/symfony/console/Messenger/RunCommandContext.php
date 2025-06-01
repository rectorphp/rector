<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202506\Symfony\Component\Console\Messenger;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunCommandContext
{
    /**
     * @readonly
     */
    public RunCommandMessage $message;
    /**
     * @readonly
     */
    public int $exitCode;
    /**
     * @readonly
     */
    public string $output;
    public function __construct(RunCommandMessage $message, int $exitCode, string $output)
    {
        $this->message = $message;
        $this->exitCode = $exitCode;
        $this->output = $output;
    }
}
