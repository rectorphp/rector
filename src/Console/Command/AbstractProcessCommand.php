<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use RectorPrefix202211\Symfony\Component\Console\Command\Command;
use RectorPrefix202211\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix202211\Symfony\Component\Console\Input\InputOption;
use RectorPrefix202211\Symfony\Contracts\Service\Attribute\Required;
abstract class AbstractProcessCommand extends Command
{
    /**
     * @var \Rector\Core\Configuration\ConfigurationFactory
     */
    protected $configurationFactory;
    /**
     * @required
     */
    public function autowire(ConfigurationFactory $configurationFactory) : void
    {
        $this->configurationFactory = $configurationFactory;
    }
    protected function configure() : void
    {
        $this->addArgument(Option::SOURCE, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Files or directories to be upgraded.');
        $this->addOption(Option::DRY_RUN, Option::DRY_RUN_SHORT, InputOption::VALUE_NONE, 'Only see the diff of changes, do not save them to files.');
        $this->addOption(Option::AUTOLOAD_FILE, Option::AUTOLOAD_FILE_SHORT, InputOption::VALUE_REQUIRED, 'Path to file with extra autoload (will be included)');
        $this->addOption(Option::NO_PROGRESS_BAR, null, InputOption::VALUE_NONE, 'Hide progress bar. Useful e.g. for nicer CI output.');
        $this->addOption(Option::NO_DIFFS, null, InputOption::VALUE_NONE, 'Hide diffs of changed files. Useful e.g. for nicer CI output.');
        $this->addOption(Option::OUTPUT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Select output format', ConsoleOutputFormatter::NAME);
        $this->addOption(Option::DEBUG, null, InputOption::VALUE_NONE, 'Display debug output.');
        $this->addOption(Option::MEMORY_LIMIT, null, InputOption::VALUE_REQUIRED, 'Memory limit for process');
        $this->addOption(Option::CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear unchaged files cache');
        $this->addOption(Option::PARALLEL_PORT, null, InputOption::VALUE_REQUIRED);
        $this->addOption(Option::PARALLEL_IDENTIFIER, null, InputOption::VALUE_REQUIRED);
    }
}
