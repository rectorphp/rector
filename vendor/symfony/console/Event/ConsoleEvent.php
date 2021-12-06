<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211206\Symfony\Component\Console\Event;

use RectorPrefix20211206\Symfony\Component\Console\Command\Command;
use RectorPrefix20211206\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211206\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20211206\Symfony\Contracts\EventDispatcher\Event;
/**
 * Allows to inspect input and output of a command.
 *
 * @author Francesco Levorato <git@flevour.net>
 */
class ConsoleEvent extends \RectorPrefix20211206\Symfony\Contracts\EventDispatcher\Event
{
    protected $command;
    private $input;
    private $output;
    public function __construct(?\RectorPrefix20211206\Symfony\Component\Console\Command\Command $command, \RectorPrefix20211206\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20211206\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }
    /**
     * Gets the command that is executed.
     *
     * @return Command|null
     */
    public function getCommand()
    {
        return $this->command;
    }
    /**
     * Gets the input instance.
     *
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }
    /**
     * Gets the output instance.
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}
