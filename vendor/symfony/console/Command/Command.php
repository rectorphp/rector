<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202507\Symfony\Component\Console\Command;

use RectorPrefix202507\Symfony\Component\Console\Application;
use RectorPrefix202507\Symfony\Component\Console\Attribute\AsCommand;
use RectorPrefix202507\Symfony\Component\Console\Completion\CompletionInput;
use RectorPrefix202507\Symfony\Component\Console\Completion\CompletionSuggestions;
use RectorPrefix202507\Symfony\Component\Console\Completion\Suggestion;
use RectorPrefix202507\Symfony\Component\Console\Exception\ExceptionInterface;
use RectorPrefix202507\Symfony\Component\Console\Exception\InvalidArgumentException;
use RectorPrefix202507\Symfony\Component\Console\Exception\LogicException;
use RectorPrefix202507\Symfony\Component\Console\Helper\HelperInterface;
use RectorPrefix202507\Symfony\Component\Console\Helper\HelperSet;
use RectorPrefix202507\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix202507\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix202507\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202507\Symfony\Component\Console\Input\InputOption;
use RectorPrefix202507\Symfony\Component\Console\Output\OutputInterface;
/**
 * Base class for all commands.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Command
{
    // see https://tldp.org/LDP/abs/html/exitcodes.html
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;
    /**
     * @var string|null The default command name
     *
     * @deprecated since Symfony 6.1, use the AsCommand attribute instead
     */
    protected static $defaultName;
    /**
     * @var string|null The default command description
     *
     * @deprecated since Symfony 6.1, use the AsCommand attribute instead
     */
    protected static $defaultDescription;
    private ?Application $application = null;
    private ?string $name = null;
    private ?string $processTitle = null;
    private array $aliases = [];
    private InputDefinition $definition;
    private bool $hidden = \false;
    private string $help = '';
    private string $description = '';
    private ?InputDefinition $fullDefinition = null;
    private bool $ignoreValidationErrors = \false;
    private ?\Closure $code = null;
    private array $synopsis = [];
    private array $usages = [];
    private ?HelperSet $helperSet = null;
    public static function getDefaultName() : ?string
    {
        $class = static::class;
        if ($attribute = \method_exists(new \ReflectionClass($class), 'getAttributes') ? (new \ReflectionClass($class))->getAttributes(AsCommand::class) : []) {
            return $attribute[0]->newInstance()->name;
        }
        $r = new \ReflectionProperty($class, 'defaultName');
        $r->setAccessible(\true);
        if ($class !== $r->class || null === static::$defaultName) {
            return null;
        }
        trigger_deprecation('symfony/console', '6.1', 'Relying on the static property "$defaultName" for setting a command name is deprecated. Add the "%s" attribute to the "%s" class instead.', AsCommand::class, static::class);
        return static::$defaultName;
    }
    public static function getDefaultDescription() : ?string
    {
        $class = static::class;
        if ($attribute = \method_exists(new \ReflectionClass($class), 'getAttributes') ? (new \ReflectionClass($class))->getAttributes(AsCommand::class) : []) {
            return $attribute[0]->newInstance()->description;
        }
        $r = new \ReflectionProperty($class, 'defaultDescription');
        $r->setAccessible(\true);
        if ($class !== $r->class || null === static::$defaultDescription) {
            return null;
        }
        trigger_deprecation('symfony/console', '6.1', 'Relying on the static property "$defaultDescription" for setting a command description is deprecated. Add the "%s" attribute to the "%s" class instead.', AsCommand::class, static::class);
        return static::$defaultDescription;
    }
    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws LogicException When the command name is empty
     */
    public function __construct(?string $name = null)
    {
        $this->definition = new InputDefinition();
        if (null === $name && null !== ($name = static::getDefaultName())) {
            $aliases = \explode('|', $name);
            if ('' === ($name = \array_shift($aliases))) {
                $this->setHidden(\true);
                $name = \array_shift($aliases);
            }
            $this->setAliases($aliases);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if ('' === $this->description) {
            $this->setDescription(static::getDefaultDescription() ?? '');
        }
        $this->configure();
    }
    /**
     * Ignores validation errors.
     *
     * This is mainly useful for the help command.
     *
     * @return void
     */
    public function ignoreValidationErrors()
    {
        $this->ignoreValidationErrors = \true;
    }
    /**
     * @return void
     */
    public function setApplication(?Application $application = null)
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        $this->application = $application;
        if ($application) {
            $this->setHelperSet($application->getHelperSet());
        } else {
            $this->helperSet = null;
        }
        $this->fullDefinition = null;
    }
    /**
     * @return void
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }
    /**
     * Gets the helper set.
     */
    public function getHelperSet() : ?HelperSet
    {
        return $this->helperSet;
    }
    /**
     * Gets the application instance for this command.
     */
    public function getApplication() : ?Application
    {
        return $this->application;
    }
    /**
     * Checks whether the command is enabled or not in the current environment.
     *
     * Override this to check for x or y and return false if the command cannot
     * run properly under the current conditions.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return \true;
    }
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
    }
    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new LogicException('You must override the execute() method in the concrete command class.');
    }
    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }
    /**
     * Runs the command.
     *
     * The code to execute is either defined directly with the
     * setCode() method or by overriding the execute() method
     * in a sub-class.
     *
     * @return int The command exit code
     *
     * @throws ExceptionInterface When input binding fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @see setCode()
     * @see execute()
     */
    public function run(InputInterface $input, OutputInterface $output) : int
    {
        // add the application arguments and options
        $this->mergeApplicationDefinition();
        // bind the input against the command specific arguments/options
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors) {
                throw $e;
            }
        }
        $this->initialize($input, $output);
        if (null !== $this->processTitle) {
            if (\function_exists('cli_set_process_title')) {
                if (!@\cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === \PHP_OS) {
                        $output->writeln('<comment>Running "cli_set_process_title" as an unprivileged user is not supported on MacOS.</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    } else {
                        \cli_set_process_title($this->processTitle);
                    }
                }
            } elseif (\function_exists('setproctitle')) {
                \setproctitle($this->processTitle);
            } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }
        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }
        // The command name argument is often omitted when a command is executed directly with its run() method.
        // It would fail the validation if we didn't make sure the command argument is present,
        // since it's required by the application.
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }
        $input->validate();
        if ($this->code) {
            $statusCode = ($this->code)($input, $output);
        } else {
            $statusCode = $this->execute($input, $output);
            if (!\is_int($statusCode)) {
                throw new \TypeError(\sprintf('Return value of "%s::execute()" must be of the type int, "%s" returned.', static::class, \get_debug_type($statusCode)));
            }
        }
        return \is_numeric($statusCode) ? (int) $statusCode : 0;
    }
    /**
     * Adds suggestions to $suggestions for the current completion input (e.g. option or argument).
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        $definition = $this->getDefinition();
        if (CompletionInput::TYPE_OPTION_VALUE === $input->getCompletionType() && $definition->hasOption($input->getCompletionName())) {
            $definition->getOption($input->getCompletionName())->complete($input, $suggestions);
        } elseif (CompletionInput::TYPE_ARGUMENT_VALUE === $input->getCompletionType() && $definition->hasArgument($input->getCompletionName())) {
            $definition->getArgument($input->getCompletionName())->complete($input, $suggestions);
        }
    }
    /**
     * Sets the code to execute when running this command.
     *
     * If this method is used, it overrides the code defined
     * in the execute() method.
     *
     * @param callable $code A callable(InputInterface $input, OutputInterface $output)
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     *
     * @see execute()
     */
    public function setCode(callable $code)
    {
        if ($code instanceof \Closure) {
            $r = new \ReflectionFunction($code);
            if (null === $r->getClosureThis()) {
                \set_error_handler(static function () {
                });
                try {
                    if ($c = \Closure::bind($code, $this)) {
                        $code = $c;
                    }
                } finally {
                    \restore_error_handler();
                }
            }
        } else {
            $code = \Closure::fromCallable($code);
        }
        $this->code = $code;
        return $this;
    }
    /**
     * Merges the application definition with the command definition.
     *
     * This method is not part of public API and should not be used directly.
     *
     * @param bool $mergeArgs Whether to merge or not the Application definition arguments to Command definition arguments
     *
     * @internal
     */
    public function mergeApplicationDefinition(bool $mergeArgs = \true) : void
    {
        if (null === $this->application) {
            return;
        }
        $this->fullDefinition = new InputDefinition();
        $this->fullDefinition->setOptions($this->definition->getOptions());
        $this->fullDefinition->addOptions($this->application->getDefinition()->getOptions());
        if ($mergeArgs) {
            $this->fullDefinition->setArguments($this->application->getDefinition()->getArguments());
            $this->fullDefinition->addArguments($this->definition->getArguments());
        } else {
            $this->fullDefinition->setArguments($this->definition->getArguments());
        }
    }
    /**
     * Sets an array of argument and option instances.
     *
     * @return $this
     * @param mixed[]|\Symfony\Component\Console\Input\InputDefinition $definition
     */
    public function setDefinition($definition)
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
        $this->fullDefinition = null;
        return $this;
    }
    /**
     * Gets the InputDefinition attached to this Command.
     */
    public function getDefinition() : InputDefinition
    {
        return $this->fullDefinition ?? $this->getNativeDefinition();
    }
    /**
     * Gets the InputDefinition to be used to create representations of this Command.
     *
     * Can be overridden to provide the original command representation when it would otherwise
     * be changed by merging with the application InputDefinition.
     *
     * This method is not part of public API and should not be used directly.
     */
    public function getNativeDefinition() : InputDefinition
    {
        if (!isset($this->definition)) {
            throw new LogicException(\sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', static::class));
        }
        return $this->definition;
    }
    /**
     * Adds an argument.
     *
     * @param $mode    The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param $default The default value (for InputArgument::OPTIONAL mode only)
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     *
     * @return $this
     *
     * @throws InvalidArgumentException When argument mode is not valid
     * @param mixed $default
     */
    public function addArgument(string $name, ?int $mode = null, string $description = '', $default = null)
    {
        $suggestedValues = 5 <= \func_num_args() ? \func_get_arg(4) : [];
        if (!\is_array($suggestedValues) && !$suggestedValues instanceof \Closure) {
            throw new \TypeError(\sprintf('Argument 5 passed to "%s()" must be array or \\Closure, "%s" given.', __METHOD__, \get_debug_type($suggestedValues)));
        }
        $this->definition->addArgument(new InputArgument($name, $mode, $description, $default, $suggestedValues));
        ($nullsafeVariable1 = $this->fullDefinition) ? $nullsafeVariable1->addArgument(new InputArgument($name, $mode, $description, $default, $suggestedValues)) : null;
        return $this;
    }
    /**
     * Adds an option.
     *
     * @param $shortcut The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
     * @param $mode     The option mode: One of the InputOption::VALUE_* constants
     * @param $default  The default value (must be null for InputOption::VALUE_NONE)
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     *
     * @return $this
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     * @param string|mixed[]|null $shortcut
     * @param mixed $default
     */
    public function addOption(string $name, $shortcut = null, ?int $mode = null, string $description = '', $default = null)
    {
        $suggestedValues = 6 <= \func_num_args() ? \func_get_arg(5) : [];
        if (!\is_array($suggestedValues) && !$suggestedValues instanceof \Closure) {
            throw new \TypeError(\sprintf('Argument 5 passed to "%s()" must be array or \\Closure, "%s" given.', __METHOD__, \get_debug_type($suggestedValues)));
        }
        $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default, $suggestedValues));
        ($nullsafeVariable2 = $this->fullDefinition) ? $nullsafeVariable2->addOption(new InputOption($name, $shortcut, $mode, $description, $default, $suggestedValues)) : null;
        return $this;
    }
    /**
     * Sets the name of the command.
     *
     * This method can set both the namespace and the name if
     * you separate them by a colon (:)
     *
     *     $command->setName('foo:bar');
     *
     * @return $this
     *
     * @throws InvalidArgumentException When the name is invalid
     */
    public function setName(string $name)
    {
        $this->validateName($name);
        $this->name = $name;
        return $this;
    }
    /**
     * Sets the process title of the command.
     *
     * This feature should be used only when creating a long process command,
     * like a daemon.
     *
     * @return $this
     */
    public function setProcessTitle(string $title)
    {
        $this->processTitle = $title;
        return $this;
    }
    /**
     * Returns the command name.
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * @param bool $hidden Whether or not the command should be hidden from the list of commands
     *
     * @return $this
     */
    public function setHidden(bool $hidden = \true)
    {
        $this->hidden = $hidden;
        return $this;
    }
    /**
     * @return bool whether the command should be publicly shown or not
     */
    public function isHidden() : bool
    {
        return $this->hidden;
    }
    /**
     * Sets the description for the command.
     *
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }
    /**
     * Returns the description for the command.
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    /**
     * Sets the help for the command.
     *
     * @return $this
     */
    public function setHelp(string $help)
    {
        $this->help = $help;
        return $this;
    }
    /**
     * Returns the help for the command.
     */
    public function getHelp() : string
    {
        return $this->help;
    }
    /**
     * Returns the processed help for the command replacing the %command.name% and
     * %command.full_name% patterns with the real values dynamically.
     */
    public function getProcessedHelp() : string
    {
        $name = $this->name;
        $isSingleCommand = ($nullsafeVariable3 = $this->application) ? $nullsafeVariable3->isSingleCommand() : null;
        $placeholders = ['%command.name%', '%command.full_name%'];
        $replacements = [$name, $isSingleCommand ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'] . ' ' . $name];
        return \str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
    }
    /**
     * Sets the aliases for the command.
     *
     * @param string[] $aliases An array of aliases for the command
     *
     * @return $this
     *
     * @throws InvalidArgumentException When an alias is invalid
     */
    public function setAliases(iterable $aliases)
    {
        $list = [];
        foreach ($aliases as $alias) {
            $this->validateName($alias);
            $list[] = $alias;
        }
        $this->aliases = \is_array($aliases) ? $aliases : $list;
        return $this;
    }
    /**
     * Returns the aliases for the command.
     */
    public function getAliases() : array
    {
        return $this->aliases;
    }
    /**
     * Returns the synopsis for the command.
     *
     * @param bool $short Whether to show the short version of the synopsis (with options folded) or not
     */
    public function getSynopsis(bool $short = \false) : string
    {
        $key = $short ? 'short' : 'long';
        if (!isset($this->synopsis[$key])) {
            $this->synopsis[$key] = \trim(\sprintf('%s %s', $this->name, $this->definition->getSynopsis($short)));
        }
        return $this->synopsis[$key];
    }
    /**
     * Add a command usage example, it'll be prefixed with the command name.
     *
     * @return $this
     */
    public function addUsage(string $usage)
    {
        if (\strncmp($usage, $this->name, \strlen($this->name)) !== 0) {
            $usage = \sprintf('%s %s', $this->name, $usage);
        }
        $this->usages[] = $usage;
        return $this;
    }
    /**
     * Returns alternative usages of the command.
     */
    public function getUsages() : array
    {
        return $this->usages;
    }
    /**
     * Gets a helper instance by name.
     *
     * @return HelperInterface
     *
     * @throws LogicException           if no HelperSet is defined
     * @throws InvalidArgumentException if the helper is not defined
     */
    public function getHelper(string $name)
    {
        if (null === $this->helperSet) {
            throw new LogicException(\sprintf('Cannot retrieve helper "%s" because there is no HelperSet defined. Did you forget to add your command to the application or to set the application on the command using the setApplication() method? You can also set the HelperSet directly using the setHelperSet() method.', $name));
        }
        return $this->helperSet->get($name);
    }
    /**
     * Validates a command name.
     *
     * It must be non-empty and parts can optionally be separated by ":".
     *
     * @throws InvalidArgumentException When the name is invalid
     */
    private function validateName(string $name) : void
    {
        if (!\preg_match('/^[^\\:]++(\\:[^\\:]++)*$/', $name)) {
            throw new InvalidArgumentException(\sprintf('Command name "%s" is invalid.', $name));
        }
    }
}
