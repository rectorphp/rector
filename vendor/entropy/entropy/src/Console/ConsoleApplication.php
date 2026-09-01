<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202609\Entropy\Console\Enum\ExitCode;
use RectorPrefix202609\Entropy\Console\Input\InputParser;
use RectorPrefix202609\Entropy\Console\Mapper\CLIRequestMapper;
use RectorPrefix202609\Entropy\Console\Output\CommandHelpFactory;
use RectorPrefix202609\Entropy\Console\Output\HelpPrinter;
use RectorPrefix202609\Entropy\Console\Output\OutputPrinter;
use RectorPrefix202609\Entropy\Tests\Console\ConsoleApplication\ConsoleApplicationTest;
use Throwable;
final class ConsoleApplication
{
    /**
     * @readonly
     */
    private HelpPrinter $helpPrinter;
    /**
     * @readonly
     */
    private OutputPrinter $outputPrinter;
    /**
     * @readonly
     */
    private CommandHelpFactory $commandHelpFactory;
    /**
     * @readonly
     */
    private InputParser $inputParser;
    /**
     * @readonly
     */
    private CommandRegistry $commandRegistry;
    /**
     * @readonly
     */
    private CLIRequestMapper $cliRequestMapper;
    public function __construct(HelpPrinter $helpPrinter, OutputPrinter $outputPrinter, CommandHelpFactory $commandHelpFactory, InputParser $inputParser, CommandRegistry $commandRegistry, CLIRequestMapper $cliRequestMapper)
    {
        $this->helpPrinter = $helpPrinter;
        $this->outputPrinter = $outputPrinter;
        $this->commandHelpFactory = $commandHelpFactory;
        $this->inputParser = $inputParser;
        $this->commandRegistry = $commandRegistry;
        $this->cliRequestMapper = $cliRequestMapper;
    }
    /**
     * @param mixed[] $argv
     * @return ExitCode::*
     */
    public function run(array $argv): int
    {
        $cliRequest = $this->inputParser->parse($argv);
        $commandName = $cliRequest->getCommandName();
        // no command name given - fall back to the default command, or show help
        if ($commandName === null) {
            $defaultCommand = $this->commandRegistry->getDefault();
            $wantsHelp = array_intersect(['h', 'help'], array_keys($cliRequest->getOptions())) !== [];
            if (!$defaultCommand instanceof CommandInterface || $wantsHelp) {
                $this->helpPrinter->print();
                return ExitCode::SUCCESS;
            }
            $commandName = $defaultCommand->getName();
        }
        if (!$this->commandRegistry->has($commandName)) {
            $defaultCommand = $this->commandRegistry->getDefault();
            // with a default command, an unknown leading token is its first argument (e.g. "ecs src")
            if (!$defaultCommand instanceof CommandInterface) {
                fwrite(\STDERR, sprintf("Unknown command: %s\n\n", $commandName));
                $this->helpPrinter->print();
                return ExitCode::INVALID_COMMAND;
            }
            $cliRequest = $cliRequest->withCommandNameAndPrependedArgument($defaultCommand->getName(), $commandName);
            $commandName = $defaultCommand->getName();
        }
        try {
            $command = $this->commandRegistry->get($commandName);
            if ($cliRequest->isCommandHelp()) {
                // build command help here :)
                $commandHelp = $this->commandHelpFactory->build($command);
                $this->outputPrinter->writeln($commandHelp);
                return ExitCode::SUCCESS;
            }
            $runArguments = $this->cliRequestMapper->resolveArguments($command, $cliRequest);
            return $command->run(...$runArguments);
        } catch (Throwable $throwable) {
            $this->outputPrinter->redBackground('Run failed: ' . $throwable->getMessage());
            $this->outputPrinter->newline();
            $this->outputPrinter->writeln($throwable->getTraceAsString());
            return ExitCode::ERROR;
        }
    }
}
