<?php

declare (strict_types=1);
namespace Rector\Console;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Configuration\Option;
use RectorPrefix202506\Symfony\Component\Console\Command\Command;
use RectorPrefix202506\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix202506\Symfony\Component\Console\Input\InputOption;
final class ProcessConfigureDecorator
{
    public static function decorate(Command $command) : void
    {
        $command->addArgument(Option::SOURCE, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Files or directories to be upgraded.');
        $command->addOption(Option::DRY_RUN, Option::DRY_RUN_SHORT, InputOption::VALUE_NONE, 'Only see the diff of changes, do not save them to files.');
        $command->addOption(Option::AUTOLOAD_FILE, Option::AUTOLOAD_FILE_SHORT, InputOption::VALUE_REQUIRED, 'Path to file with extra autoload (will be included)');
        $command->addOption(Option::NO_PROGRESS_BAR, null, InputOption::VALUE_NONE, 'Hide progress bar. Useful e.g. for nicer CI output.');
        $command->addOption(Option::NO_DIFFS, null, InputOption::VALUE_NONE, 'Hide diffs of changed files. Useful e.g. for nicer CI output.');
        $command->addOption(Option::OUTPUT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Select output format', ConsoleOutputFormatter::NAME);
        // filter by rule and path
        $command->addOption(Option::ONLY, null, InputOption::VALUE_REQUIRED, 'Fully qualified rule class name');
        $command->addOption(Option::ONLY_SUFFIX, null, InputOption::VALUE_REQUIRED, 'Filter only files with specific suffix in name, e.g. "Controller"');
        $command->addOption(Option::KAIZEN, null, InputOption::VALUE_REQUIRED, 'Improve step by step: apply only first X rules that make a change');
        $command->addOption(Option::DEBUG, null, InputOption::VALUE_NONE, 'Display debug output.');
        $command->addOption(Option::MEMORY_LIMIT, null, InputOption::VALUE_REQUIRED, 'Memory limit for process');
        $command->addOption(Option::CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear unchanged files cache');
        $command->addOption(Option::PARALLEL_PORT, null, InputOption::VALUE_REQUIRED);
        $command->addOption(Option::PARALLEL_IDENTIFIER, null, InputOption::VALUE_REQUIRED);
        $command->addOption(Option::XDEBUG, null, InputOption::VALUE_NONE, 'Display xdebug output.');
    }
}
