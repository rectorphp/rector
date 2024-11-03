<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202411\Symfony\Component\Console\Command;

use RectorPrefix202411\Symfony\Component\Console\Application;
use RectorPrefix202411\Symfony\Component\Console\Completion\CompletionInput;
use RectorPrefix202411\Symfony\Component\Console\Completion\CompletionSuggestions;
use RectorPrefix202411\Symfony\Component\Console\Helper\HelperInterface;
use RectorPrefix202411\Symfony\Component\Console\Helper\HelperSet;
use RectorPrefix202411\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix202411\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202411\Symfony\Component\Console\Output\ConsoleOutputInterface;
use RectorPrefix202411\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202411\Symfony\Component\Stopwatch\Stopwatch;
/**
 * @internal
 *
 * @author Jules Pietri <jules@heahprod.com>
 */
final class TraceableCommand extends Command implements SignalableCommandInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Command\Command
     */
    public $command;
    /**
     * @var int
     */
    public $exitCode;
    /**
     * @var int|null
     */
    public $interruptedBySignal;
    /**
     * @var bool
     */
    public $ignoreValidation;
    /**
     * @var bool
     */
    public $isInteractive = \false;
    /**
     * @var string
     */
    public $duration = 'n/a';
    /**
     * @var string
     */
    public $maxMemoryUsage = 'n/a';
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    public $input;
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    public $output;
    /** @var array<string, mixed> */
    public $arguments;
    /** @var array<string, mixed> */
    public $options;
    /** @var array<string, mixed> */
    public $interactiveInputs = [];
    /**
     * @var mixed[]
     */
    public $handledSignals = [];
    public function __construct(Command $command, Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
        if ($command instanceof LazyCommand) {
            $command = $command->getCommand();
        }
        $this->command = $command;
        // prevent call to self::getDefaultDescription()
        $this->setDescription($command->getDescription());
        parent::__construct($command->getName());
        // init below enables calling {@see parent::run()}
        [$code, $processTitle, $ignoreValidationErrors] = \Closure::bind(function () {
            return [$this->code, $this->processTitle, $this->ignoreValidationErrors];
        }, $command, Command::class)();
        if (\is_callable($code)) {
            $this->setCode($code);
        }
        if ($processTitle) {
            parent::setProcessTitle($processTitle);
        }
        if ($ignoreValidationErrors) {
            parent::ignoreValidationErrors();
        }
        $this->ignoreValidation = $ignoreValidationErrors;
    }
    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->command->{$name}(...$arguments);
    }
    public function getSubscribedSignals() : array
    {
        return $this->command instanceof SignalableCommandInterface ? $this->command->getSubscribedSignals() : [];
    }
    /**
     * @param int|false $previousExitCode
     * @return int|false
     */
    public function handleSignal(int $signal, $previousExitCode = 0)
    {
        if (!$this->command instanceof SignalableCommandInterface) {
            return \false;
        }
        $event = $this->stopwatch->start($this->getName() . '.handle_signal');
        $exit = $this->command->handleSignal($signal, $previousExitCode);
        $event->stop();
        if (!isset($this->handledSignals[$signal])) {
            $this->handledSignals[$signal] = ['handled' => 0, 'duration' => 0, 'memory' => 0];
        }
        ++$this->handledSignals[$signal]['handled'];
        $this->handledSignals[$signal]['duration'] += $event->getDuration();
        $this->handledSignals[$signal]['memory'] = \max($this->handledSignals[$signal]['memory'], $event->getMemory() >> 20);
        return $exit;
    }
    /**
     * {@inheritdoc}
     *
     * Calling parent method is required to be used in {@see parent::run()}.
     */
    public function ignoreValidationErrors() : void
    {
        $this->ignoreValidation = \true;
        $this->command->ignoreValidationErrors();
        parent::ignoreValidationErrors();
    }
    public function setApplication(?Application $application = null) : void
    {
        $this->command->setApplication($application);
    }
    public function getApplication() : ?Application
    {
        return $this->command->getApplication();
    }
    public function setHelperSet(HelperSet $helperSet) : void
    {
        $this->command->setHelperSet($helperSet);
    }
    public function getHelperSet() : ?HelperSet
    {
        return $this->command->getHelperSet();
    }
    public function isEnabled() : bool
    {
        return $this->command->isEnabled();
    }
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        $this->command->complete($input, $suggestions);
    }
    /**
     * {@inheritdoc}
     *
     * Calling parent method is required to be used in {@see parent::run()}.
     * @return static
     */
    public function setCode(callable $code)
    {
        $this->command->setCode($code);
        return parent::setCode(function (InputInterface $input, OutputInterface $output) use($code) : int {
            $event = $this->stopwatch->start($this->getName() . '.code');
            $this->exitCode = $code($input, $output);
            $event->stop();
            return $this->exitCode;
        });
    }
    /**
     * @internal
     */
    public function mergeApplicationDefinition(bool $mergeArgs = \true) : void
    {
        $this->command->mergeApplicationDefinition($mergeArgs);
    }
    /**
     * @param mixed[]|\Symfony\Component\Console\Input\InputDefinition $definition
     * @return static
     */
    public function setDefinition($definition)
    {
        $this->command->setDefinition($definition);
        return $this;
    }
    public function getDefinition() : InputDefinition
    {
        return $this->command->getDefinition();
    }
    public function getNativeDefinition() : InputDefinition
    {
        return $this->command->getNativeDefinition();
    }
    /**
     * @param mixed[]|\Closure $suggestedValues
     * @param mixed $default
     * @return static
     */
    public function addArgument(string $name, ?int $mode = null, string $description = '', $default = null, $suggestedValues = [])
    {
        $this->command->addArgument($name, $mode, $description, $default, $suggestedValues);
        return $this;
    }
    /**
     * @param string|mixed[]|null $shortcut
     * @param mixed[]|\Closure $suggestedValues
     * @param mixed $default
     * @return static
     */
    public function addOption(string $name, $shortcut = null, ?int $mode = null, string $description = '', $default = null, $suggestedValues = [])
    {
        $this->command->addOption($name, $shortcut, $mode, $description, $default, $suggestedValues);
        return $this;
    }
    /**
     * {@inheritdoc}
     *
     * Calling parent method is required to be used in {@see parent::run()}.
     * @return static
     */
    public function setProcessTitle(string $title)
    {
        $this->command->setProcessTitle($title);
        return parent::setProcessTitle($title);
    }
    /**
     * @return static
     */
    public function setHelp(string $help)
    {
        $this->command->setHelp($help);
        return $this;
    }
    public function getHelp() : string
    {
        return $this->command->getHelp();
    }
    public function getProcessedHelp() : string
    {
        return $this->command->getProcessedHelp();
    }
    public function getSynopsis(bool $short = \false) : string
    {
        return $this->command->getSynopsis($short);
    }
    /**
     * @return static
     */
    public function addUsage(string $usage)
    {
        $this->command->addUsage($usage);
        return $this;
    }
    public function getUsages() : array
    {
        return $this->command->getUsages();
    }
    public function getHelper(string $name) : HelperInterface
    {
        return $this->command->getHelper($name);
    }
    public function run(InputInterface $input, OutputInterface $output) : int
    {
        $this->input = $input;
        $this->output = $output;
        $this->arguments = $input->getArguments();
        $this->options = $input->getOptions();
        $event = $this->stopwatch->start($this->getName(), 'command');
        try {
            $this->exitCode = parent::run($input, $output);
        } finally {
            $event->stop();
            if ($output instanceof ConsoleOutputInterface && $output->isDebug()) {
                $output->getErrorOutput()->writeln((string) $event);
            }
            $this->duration = $event->getDuration() . ' ms';
            $this->maxMemoryUsage = ($event->getMemory() >> 20) . ' MiB';
            if ($this->isInteractive) {
                $this->extractInteractiveInputs($input->getArguments(), $input->getOptions());
            }
        }
        return $this->exitCode;
    }
    protected function initialize(InputInterface $input, OutputInterface $output) : void
    {
        $event = $this->stopwatch->start($this->getName() . '.init', 'command');
        $this->command->initialize($input, $output);
        $event->stop();
    }
    protected function interact(InputInterface $input, OutputInterface $output) : void
    {
        if (!($this->isInteractive = Command::class !== (new \ReflectionMethod($this->command, 'interact'))->getDeclaringClass()->getName())) {
            return;
        }
        $event = $this->stopwatch->start($this->getName() . '.interact', 'command');
        $this->command->interact($input, $output);
        $event->stop();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $event = $this->stopwatch->start($this->getName() . '.execute', 'command');
        $exitCode = $this->command->execute($input, $output);
        $event->stop();
        return $exitCode;
    }
    private function extractInteractiveInputs(array $arguments, array $options) : void
    {
        foreach ($arguments as $argName => $argValue) {
            if (\array_key_exists($argName, $this->arguments) && $this->arguments[$argName] === $argValue) {
                continue;
            }
            $this->interactiveInputs[$argName] = $argValue;
        }
        foreach ($options as $optName => $optValue) {
            if (\array_key_exists($optName, $this->options) && $this->options[$optName] === $optValue) {
                continue;
            }
            $this->interactiveInputs['--' . $optName] = $optValue;
        }
    }
}
