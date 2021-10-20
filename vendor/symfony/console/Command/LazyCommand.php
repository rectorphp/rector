<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Command;

use RectorPrefix20211020\Symfony\Component\Console\Application;
use RectorPrefix20211020\Symfony\Component\Console\Helper\HelperSet;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211020\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class LazyCommand extends \RectorPrefix20211020\Symfony\Component\Console\Command\Command
{
    private $command;
    private $isEnabled;
    public function __construct(string $name, array $aliases, string $description, bool $isHidden, \Closure $commandFactory, ?bool $isEnabled = \true)
    {
        $this->setName($name)->setAliases($aliases)->setHidden($isHidden)->setDescription($description);
        $this->command = $commandFactory;
        $this->isEnabled = $isEnabled;
    }
    public function ignoreValidationErrors() : void
    {
        $this->getCommand()->ignoreValidationErrors();
    }
    /**
     * @param \Symfony\Component\Console\Application|null $application
     */
    public function setApplication($application = null) : void
    {
        if ($this->command instanceof parent) {
            $this->command->setApplication($application);
        }
        parent::setApplication($application);
    }
    /**
     * @param \Symfony\Component\Console\Helper\HelperSet $helperSet
     */
    public function setHelperSet($helperSet) : void
    {
        if ($this->command instanceof parent) {
            $this->command->setHelperSet($helperSet);
        }
        parent::setHelperSet($helperSet);
    }
    public function isEnabled() : bool
    {
        return $this->isEnabled ?? $this->getCommand()->isEnabled();
    }
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function run($input, $output) : int
    {
        return $this->getCommand()->run($input, $output);
    }
    /**
     * @return $this
     * @param callable $code
     */
    public function setCode($code) : self
    {
        $this->getCommand()->setCode($code);
        return $this;
    }
    /**
     * @internal
     * @param bool $mergeArgs
     */
    public function mergeApplicationDefinition($mergeArgs = \true) : void
    {
        $this->getCommand()->mergeApplicationDefinition($mergeArgs);
    }
    /**
     * @return $this
     */
    public function setDefinition($definition) : self
    {
        $this->getCommand()->setDefinition($definition);
        return $this;
    }
    public function getDefinition() : \RectorPrefix20211020\Symfony\Component\Console\Input\InputDefinition
    {
        return $this->getCommand()->getDefinition();
    }
    public function getNativeDefinition() : \RectorPrefix20211020\Symfony\Component\Console\Input\InputDefinition
    {
        return $this->getCommand()->getNativeDefinition();
    }
    /**
     * @return $this
     * @param string $name
     * @param int|null $mode
     * @param string $description
     */
    public function addArgument($name, $mode = null, $description = '', $default = null) : self
    {
        $this->getCommand()->addArgument($name, $mode, $description, $default);
        return $this;
    }
    /**
     * @return $this
     * @param string $name
     * @param int|null $mode
     * @param string $description
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null) : self
    {
        $this->getCommand()->addOption($name, $shortcut, $mode, $description, $default);
        return $this;
    }
    /**
     * @return $this
     * @param string $title
     */
    public function setProcessTitle($title) : self
    {
        $this->getCommand()->setProcessTitle($title);
        return $this;
    }
    /**
     * @return $this
     * @param string $help
     */
    public function setHelp($help) : self
    {
        $this->getCommand()->setHelp($help);
        return $this;
    }
    public function getHelp() : string
    {
        return $this->getCommand()->getHelp();
    }
    public function getProcessedHelp() : string
    {
        return $this->getCommand()->getProcessedHelp();
    }
    /**
     * @param bool $short
     */
    public function getSynopsis($short = \false) : string
    {
        return $this->getCommand()->getSynopsis($short);
    }
    /**
     * @return $this
     * @param string $usage
     */
    public function addUsage($usage) : self
    {
        $this->getCommand()->addUsage($usage);
        return $this;
    }
    public function getUsages() : array
    {
        return $this->getCommand()->getUsages();
    }
    /**
     * @return mixed
     * @param string $name
     */
    public function getHelper($name)
    {
        return $this->getCommand()->getHelper($name);
    }
    public function getCommand() : parent
    {
        if (!$this->command instanceof \Closure) {
            return $this->command;
        }
        $command = $this->command = ($this->command)();
        $command->setApplication($this->getApplication());
        if (null !== $this->getHelperSet()) {
            $command->setHelperSet($this->getHelperSet());
        }
        $command->setName($this->getName())->setAliases($this->getAliases())->setHidden($this->isHidden())->setDescription($this->getDescription());
        // Will throw if the command is not correctly initialized.
        $command->getDefinition();
        return $command;
    }
}
