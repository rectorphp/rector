<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use RectorPrefix20211231\Symfony\Component\Console\Command\Command;
use RectorPrefix20211231\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20211231\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20211231\Symfony\Contracts\Service\Attribute\Required;
abstract class AbstractProcessCommand extends \RectorPrefix20211231\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Rector\Core\Configuration\ConfigurationFactory
     */
    protected $configurationFactory;
    /**
     * @required
     */
    public function autowire(\Rector\Core\Configuration\ConfigurationFactory $configurationFactory) : void
    {
        $this->configurationFactory = $configurationFactory;
    }
    protected function configure() : void
    {
        $this->addArgument(\Rector\Core\Configuration\Option::SOURCE, \RectorPrefix20211231\Symfony\Component\Console\Input\InputArgument::OPTIONAL | \RectorPrefix20211231\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'Files or directories to be upgraded.');
        $this->addOption(\Rector\Core\Configuration\Option::DRY_RUN, \Rector\Core\Configuration\Option::DRY_RUN_SHORT, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Only see the diff of changes, do not save them to files.');
        $this->addOption(\Rector\Core\Configuration\Option::AUTOLOAD_FILE, \Rector\Core\Configuration\Option::AUTOLOAD_FILE_SHORT, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to file with extra autoload (will be included)');
        $this->addOption(\Rector\Core\Configuration\Option::NO_PROGRESS_BAR, null, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Hide progress bar. Useful e.g. for nicer CI output.');
        $this->addOption(\Rector\Core\Configuration\Option::NO_DIFFS, null, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Hide diffs of changed files. Useful e.g. for nicer CI output.');
        $this->addOption(\Rector\Core\Configuration\Option::OUTPUT_FORMAT, null, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Select output format', \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME);
        $this->addOption(\Rector\Core\Configuration\Option::CLEAR_CACHE, null, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Clear unchaged files cache');
        $this->addOption(\Rector\Core\Configuration\Option::PARALLEL_PORT, null, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED);
        $this->addOption(\Rector\Core\Configuration\Option::PARALLEL_IDENTIFIER, null, \RectorPrefix20211231\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED);
    }
}
