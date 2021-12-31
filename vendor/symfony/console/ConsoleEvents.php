<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Symfony\Component\Console;

use RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleCommandEvent;
use RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleErrorEvent;
use RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleSignalEvent;
use RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleTerminateEvent;
/**
 * Contains all events dispatched by an Application.
 *
 * @author Francesco Levorato <git@flevour.net>
 */
final class ConsoleEvents
{
    /**
     * The COMMAND event allows you to attach listeners before any command is
     * executed by the console. It also allows you to modify the command, input and output
     * before they are handed to the command.
     *
     * @Event("Symfony\Component\Console\Event\ConsoleCommandEvent")
     */
    public const COMMAND = 'console.command';
    /**
     * The SIGNAL event allows you to perform some actions
     * after the command execution was interrupted.
     *
     * @Event("Symfony\Component\Console\Event\ConsoleSignalEvent")
     */
    public const SIGNAL = 'console.signal';
    /**
     * The TERMINATE event allows you to attach listeners after a command is
     * executed by the console.
     *
     * @Event("Symfony\Component\Console\Event\ConsoleTerminateEvent")
     */
    public const TERMINATE = 'console.terminate';
    /**
     * The ERROR event occurs when an uncaught exception or error appears.
     *
     * This event allows you to deal with the exception/error or
     * to modify the thrown exception.
     *
     * @Event("Symfony\Component\Console\Event\ConsoleErrorEvent")
     */
    public const ERROR = 'console.error';
    /**
     * Event aliases.
     *
     * These aliases can be consumed by RegisterListenersPass.
     */
    public const ALIASES = [\RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleCommandEvent::class => self::COMMAND, \RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleErrorEvent::class => self::ERROR, \RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleSignalEvent::class => self::SIGNAL, \RectorPrefix20211231\Symfony\Component\Console\Event\ConsoleTerminateEvent::class => self::TERMINATE];
}
