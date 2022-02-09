<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220209\Symfony\Component\Console\Event;

use RectorPrefix20220209\Symfony\Component\Console\Command\Command;
use RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220209\Symfony\Contracts\EventDispatcher\Event;
/**
 * Allows to inspect input and output of a command.
 *
 * @author Francesco Levorato <git@flevour.net>
 */
class ConsoleEvent extends \RectorPrefix20220209\Symfony\Contracts\EventDispatcher\Event
{
    protected $command;
    private $input;
    private $output;
    public function __construct(?\RectorPrefix20220209\Symfony\Component\Console\Command\Command $command, \RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }
    /**
     * Gets the command that is executed.
     */
    public function getCommand() : ?\RectorPrefix20220209\Symfony\Component\Console\Command\Command
    {
        return $this->command;
    }
    /**
     * Gets the input instance.
     */
    public function getInput() : \RectorPrefix20220209\Symfony\Component\Console\Input\InputInterface
    {
        return $this->input;
    }
    /**
     * Gets the output instance.
     */
    public function getOutput() : \RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface
    {
        return $this->output;
    }
}
