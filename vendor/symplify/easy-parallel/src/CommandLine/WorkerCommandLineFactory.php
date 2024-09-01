<?php

declare (strict_types=1);
namespace RectorPrefix202409\Symplify\EasyParallel\CommandLine;

use RectorPrefix202409\Symfony\Component\Console\Command\Command;
use RectorPrefix202409\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202409\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
use RectorPrefix202409\Symplify\EasyParallel\Reflection\CommandFromReflectionFactory;
/**
 * @api
 * @see \Symplify\EasyParallel\Tests\CommandLine\WorkerCommandLineFactoryTest
 */
final class WorkerCommandLineFactory
{
    /**
     * @var string
     */
    private const OPTION_DASHES = '--';
    /**
     * These options are not relevant for nested worker command line.
     *
     * @var string[]
     */
    private const EXCLUDED_OPTION_NAMES = ['output-format'];
    /**
     * @readonly
     * @var \Symplify\EasyParallel\Reflection\CommandFromReflectionFactory
     */
    private $commandFromReflectionFactory;
    public function __construct()
    {
        $this->commandFromReflectionFactory = new CommandFromReflectionFactory();
    }
    /**
     * @param class-string<Command> $mainCommandClass
     */
    public function create(string $baseScript, string $mainCommandClass, string $workerCommandName, string $pathsOptionName, ?string $projectConfigFile, InputInterface $input, string $identifier, int $port) : string
    {
        $commandArguments = \array_slice($_SERVER['argv'], 1);
        $args = \array_merge([\PHP_BINARY, $baseScript], $commandArguments);
        $mainCommand = $this->commandFromReflectionFactory->create($mainCommandClass);
        if ($mainCommand->getName() === null) {
            $errorMessage = \sprintf('The command name for "%s" is missing', \get_class($mainCommand));
            throw new ParallelShouldNotHappenException($errorMessage);
        }
        $mainCommandName = $mainCommand->getName();
        $processCommandArray = [];
        foreach ($args as $arg) {
            // skip command name
            if ($arg === $mainCommandName) {
                break;
            }
            $processCommandArray[] = \escapeshellarg((string) $arg);
        }
        $processCommandArray[] = $workerCommandName;
        if ($projectConfigFile !== null) {
            $processCommandArray[] = '--config';
            $processCommandArray[] = \escapeshellarg($projectConfigFile);
        }
        $mainCommandOptionNames = $this->getCommandOptionNames($mainCommand);
        $processCommandOptions = $this->mirrorCommandOptions($input, $mainCommandOptionNames);
        $processCommandArray = \array_merge($processCommandArray, $processCommandOptions);
        // for TCP local server
        $processCommandArray[] = '--port';
        $processCommandArray[] = $port;
        $processCommandArray[] = '--identifier';
        $processCommandArray[] = \escapeshellarg($identifier);
        /** @var string[] $paths */
        $paths = (array) $input->getArgument($pathsOptionName);
        foreach ($paths as $path) {
            $processCommandArray[] = \escapeshellarg($path);
        }
        // set json output
        $processCommandArray[] = '--output-format';
        $processCommandArray[] = \escapeshellarg('json');
        // explicitly disable colors, breaks json_decode() otherwise
        // @see https://github.com/symfony/symfony/issues/1238
        $processCommandArray[] = '--no-ansi';
        return \implode(' ', $processCommandArray);
    }
    /**
     * @return string[]
     */
    private function getCommandOptionNames(Command $command) : array
    {
        $inputDefinition = $command->getDefinition();
        $optionNames = [];
        foreach ($inputDefinition->getOptions() as $inputOption) {
            $optionNames[] = $inputOption->getName();
        }
        return $optionNames;
    }
    /**
     * Keeps all options that are allowed in check command options
     *
     * @param string[] $mainCommandOptionNames
     * @return string[]
     */
    private function mirrorCommandOptions(InputInterface $input, array $mainCommandOptionNames) : array
    {
        $processCommandOptions = [];
        foreach ($mainCommandOptionNames as $mainCommandOptionName) {
            if ($this->shouldSkipOption($input, $mainCommandOptionName)) {
                continue;
            }
            /** @var bool|string|null $optionValue */
            $optionValue = $input->getOption($mainCommandOptionName);
            // skip clutter
            if ($optionValue === null) {
                continue;
            }
            if (\is_bool($optionValue)) {
                if ($optionValue) {
                    $processCommandOptions[] = self::OPTION_DASHES . $mainCommandOptionName;
                }
                continue;
            }
            if ($mainCommandOptionName === 'memory-limit') {
                // symfony/console does not accept -1 as value without assign
                $processCommandOptions[] = '--' . $mainCommandOptionName . '=' . $optionValue;
            } else {
                $processCommandOptions[] = self::OPTION_DASHES . $mainCommandOptionName;
                $processCommandOptions[] = \escapeshellarg($optionValue);
            }
        }
        return $processCommandOptions;
    }
    private function shouldSkipOption(InputInterface $input, string $optionName) : bool
    {
        if (!$input->hasOption($optionName)) {
            return \true;
        }
        return \in_array($optionName, self::EXCLUDED_OPTION_NAMES, \true);
    }
}
