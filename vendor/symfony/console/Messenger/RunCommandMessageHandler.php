<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202312\Symfony\Component\Console\Messenger;

use RectorPrefix202312\Symfony\Component\Console\Application;
use RectorPrefix202312\Symfony\Component\Console\Command\Command;
use RectorPrefix202312\Symfony\Component\Console\Exception\RunCommandFailedException;
use RectorPrefix202312\Symfony\Component\Console\Input\StringInput;
use RectorPrefix202312\Symfony\Component\Console\Output\BufferedOutput;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunCommandMessageHandler
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Application
     */
    private $application;
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
    public function __invoke(RunCommandMessage $message) : RunCommandContext
    {
        $input = new StringInput($message->input);
        $output = new BufferedOutput();
        $this->application->setCatchExceptions($message->catchExceptions);
        try {
            $exitCode = $this->application->run($input, $output);
        } catch (\Throwable $e) {
            throw new RunCommandFailedException($e, new RunCommandContext($message, Command::FAILURE, $output->fetch()));
        }
        if ($message->throwOnFailure && Command::SUCCESS !== $exitCode) {
            throw new RunCommandFailedException(\sprintf('Command "%s" exited with code "%s".', $message->input, $exitCode), new RunCommandContext($message, $exitCode, $output->fetch()));
        }
        return new RunCommandContext($message, $exitCode, $output->fetch());
    }
}
