<?php

declare(strict_types=1);

namespace Rector\Parallel\Command;

use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
use Symplify\EasyParallel\Reflection\CommandFromReflectionFactory;

/**
 * @see \Rector\Tests\Parallel\Command\WorkerCommandLineFactoryTest
 * @todo possibly extract to symplify/easy-parallel
 */
final class WorkerCommandLineFactory
{
    /**
     * @var string
     */
    private const OPTION_DASHES = '--';

    private readonly CommandFromReflectionFactory $commandFromReflectionFactory;

    public function __construct()
    {
        $this->commandFromReflectionFactory = new CommandFromReflectionFactory();
    }

    /**
     * @param class-string<Command> $mainCommandClass
     */
    public function create(
        string $mainScript,
        string $mainCommandClass,
        string $workerCommandName,
        ?string $projectConfigFile,
        InputInterface $input,
        string $identifier,
        int $port
    ): string {
        $commandArguments = array_slice($_SERVER['argv'], 1);
        $args = array_merge([PHP_BINARY, $mainScript], $commandArguments);

        $workerCommandArray = [];

        $mainCommand = $this->commandFromReflectionFactory->create($mainCommandClass);

        if ($mainCommand->getName() === null) {
            $errorMessage = sprintf('The command name for "%s" is missing', $mainCommand::class);
            throw new ParallelShouldNotHappenException($errorMessage);
        }

        $mainCommandName = $mainCommand->getName();
        $mainCommandNames = [$mainCommandName, $mainCommandName[0]];

        foreach ($args as $arg) {
            // skip command name
            if (in_array($arg, $mainCommandNames, true)) {
                break;
            }

            $workerCommandArray[] = escapeshellarg($arg);
        }

        $workerCommandArray[] = $workerCommandName;
        if ($projectConfigFile !== null) {
            $workerCommandArray[] = self::OPTION_DASHES . Option::CONFIG;
            $workerCommandArray[] = escapeshellarg($projectConfigFile);
        }

        $mainCommandOptionNames = $this->getCommandOptionNames($mainCommand);
        $workerCommandOptions = $this->mirrorCommandOptions($input, $mainCommandOptionNames);
        $workerCommandArray = array_merge($workerCommandArray, $workerCommandOptions);

        // for TCP local server
        $workerCommandArray[] = '--port';
        $workerCommandArray[] = $port;

        $workerCommandArray[] = '--identifier';
        $workerCommandArray[] = escapeshellarg($identifier);

        /** @var string[] $paths */
        $paths = $input->getArgument(Option::SOURCE);
        foreach ($paths as $path) {
            $workerCommandArray[] = escapeshellarg($path);
        }

        // set json output
        $workerCommandArray[] = self::OPTION_DASHES . Option::OUTPUT_FORMAT;
        $workerCommandArray[] = escapeshellarg(JsonOutputFormatter::NAME);

        // disable colors, breaks json_decode() otherwise
        // @see https://github.com/symfony/symfony/issues/1238
        $workerCommandArray[] = '--no-ansi';

        if ($input->hasOption(Option::CONFIG)) {
            $workerCommandArray[] = '--config';
            $workerCommandArray[] = $input->getOption(Option::CONFIG);
        }

        return implode(' ', $workerCommandArray);
    }

    private function shouldSkipOption(InputInterface $input, string $optionName): bool
    {
        if (! $input->hasOption($optionName)) {
            return true;
        }

        // skip output format, not relevant in parallel worker command
        return $optionName === Option::OUTPUT_FORMAT;
    }

    /**
     * @return string[]
     */
    private function getCommandOptionNames(Command $command): array
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
    private function mirrorCommandOptions(InputInterface $input, array $mainCommandOptionNames): array
    {
        $workerCommandOptions = [];

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

            if (is_bool($optionValue)) {
                if ($optionValue) {
                    $workerCommandOptions[] = self::OPTION_DASHES . $mainCommandOptionName;
                }

                continue;
            }

            $workerCommandOptions[] = self::OPTION_DASHES . $mainCommandOptionName;
            $workerCommandOptions[] = escapeshellarg($optionValue);
        }

        return $workerCommandOptions;
    }
}
