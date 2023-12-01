<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202312\Symfony\Component\Console\Command;

use RectorPrefix202312\Symfony\Component\Console\Application;
use RectorPrefix202312\Symfony\Component\Console\Completion\CompletionInput;
use RectorPrefix202312\Symfony\Component\Console\Completion\CompletionSuggestions;
use RectorPrefix202312\Symfony\Component\Console\Completion\Suggestion;
use RectorPrefix202312\Symfony\Component\Console\Helper\HelperInterface;
use RectorPrefix202312\Symfony\Component\Console\Helper\HelperSet;
use RectorPrefix202312\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix202312\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202312\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class LazyCommand extends Command
{
    /**
     * @var \Closure|\Symfony\Component\Console\Command\Command
     */
    private $command;
    /**
     * @var bool|null
     */
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
    public function setApplication(Application $application = null) : void
    {
        if (1 > \func_num_args()) {
            // \trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        if ($this->command instanceof parent) {
            $this->command->setApplication($application);
        }
        parent::setApplication($application);
    }
    public function setHelperSet(HelperSet $helperSet) : void
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
    public function run(InputInterface $input, OutputInterface $output) : int
    {
        return $this->getCommand()->run($input, $output);
    }
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        $this->getCommand()->complete($input, $suggestions);
    }
    /**
     * @return static
     */
    public function setCode(callable $code)
    {
        $this->getCommand()->setCode($code);
        return $this;
    }
    /**
     * @internal
     */
    public function mergeApplicationDefinition(bool $mergeArgs = \true) : void
    {
        $this->getCommand()->mergeApplicationDefinition($mergeArgs);
    }
    /**
     * @param mixed[]|\Symfony\Component\Console\Input\InputDefinition $definition
     * @return static
     */
    public function setDefinition($definition)
    {
        $this->getCommand()->setDefinition($definition);
        return $this;
    }
    public function getDefinition() : InputDefinition
    {
        return $this->getCommand()->getDefinition();
    }
    public function getNativeDefinition() : InputDefinition
    {
        return $this->getCommand()->getNativeDefinition();
    }
    /**
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     * @param mixed $default
     * @return static
     */
    public function addArgument(string $name, int $mode = null, string $description = '', $default = null)
    {
        $suggestedValues = 5 <= \func_num_args() ? \func_get_arg(4) : [];
        $this->getCommand()->addArgument($name, $mode, $description, $default, $suggestedValues);
        return $this;
    }
    /**
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     * @param string|mixed[] $shortcut
     * @param mixed $default
     * @return static
     */
    public function addOption(string $name, $shortcut = null, int $mode = null, string $description = '', $default = null)
    {
        $suggestedValues = 6 <= \func_num_args() ? \func_get_arg(5) : [];
        $this->getCommand()->addOption($name, $shortcut, $mode, $description, $default, $suggestedValues);
        return $this;
    }
    /**
     * @return static
     */
    public function setProcessTitle(string $title)
    {
        $this->getCommand()->setProcessTitle($title);
        return $this;
    }
    /**
     * @return static
     */
    public function setHelp(string $help)
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
    public function getSynopsis(bool $short = \false) : string
    {
        return $this->getCommand()->getSynopsis($short);
    }
    /**
     * @return static
     */
    public function addUsage(string $usage)
    {
        $this->getCommand()->addUsage($usage);
        return $this;
    }
    public function getUsages() : array
    {
        return $this->getCommand()->getUsages();
    }
    public function getHelper(string $name) : HelperInterface
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
