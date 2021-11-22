<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractProcessCommand extends Command
{
    protected ConfigurationFactory $configurationFactory;

    #[Required]
    public function autowire(ConfigurationFactory $configurationFactory,): void
    {
        $this->configurationFactory = $configurationFactory;
    }

    protected function configure(): void
    {
        $this->addArgument(
            Option::SOURCE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Files or directories to be upgraded.'
        );

        $this->addOption(
            Option::DRY_RUN,
            Option::DRY_RUN_SHORT,
            InputOption::VALUE_NONE,
            'Only see the diff of changes, do not save them to files.'
        );

        $this->addOption(
            Option::AUTOLOAD_FILE,
            Option::AUTOLOAD_FILE_SHORT,
            InputOption::VALUE_REQUIRED,
            'Path to file with extra autoload (will be included)'
        );

        $this->addOption(
            Option::NO_PROGRESS_BAR,
            null,
            InputOption::VALUE_NONE,
            'Hide progress bar. Useful e.g. for nicer CI output.'
        );

        $this->addOption(
            Option::NO_DIFFS,
            null,
            InputOption::VALUE_NONE,
            'Hide diffs of changed files. Useful e.g. for nicer CI output.'
        );

        $this->addOption(Option::CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear unchaged files cache');

        $this->addOption(Option::PARALLEL_PORT, null, InputOption::VALUE_REQUIRED);
        $this->addOption(Option::PARALLEL_IDENTIFIER, null, InputOption::VALUE_REQUIRED);
    }
}
