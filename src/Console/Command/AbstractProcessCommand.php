<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Caching\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Application\RectorApplication;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Extension\ReportingExtensionRunner;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\Guard\RectorGuard;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\Yaml\YamlProcessor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractProcessCommand extends AbstractCommand
{
    /**
     * @var string[]
     */
    protected $paths = [];

    /**
     * @var FilesFinder
     */
    protected $filesFinder;

    /**
     * @var AdditionalAutoloader
     */
    protected $additionalAutoloader;

    /**
     * @var RectorGuard
     */
    protected $rectorGuard;

    /**
     * @var ErrorAndDiffCollector
     */
    protected $errorAndDiffCollector;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var RectorApplication
     */
    protected $rectorApplication;

    /**
     * @var OutputFormatterCollector
     */
    protected $outputFormatterCollector;

    /**
     * @var ReportingExtensionRunner
     */
    protected $reportingExtensionRunner;

    /**
     * @var RectorNodeTraverser
     */
    protected $rectorNodeTraverser;

    /**
     * @var StubLoader
     */
    protected $stubLoader;

    /**
     * @var YamlProcessor
     */
    protected $yamlProcessor;

    /**
     * @var UnchangedFilesFilter
     */
    protected $unchangedFilesFilter;

    /**
     * @var ChangedFilesDetector
     */
    protected $changedFilesDetector;

    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;

    /**
     * @param string[] $paths
     */
    public function __construct(
        FilesFinder $phpFilesFinder,
        AdditionalAutoloader $additionalAutoloader,
        RectorGuard $rectorGuard,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        RectorApplication $rectorApplication,
        OutputFormatterCollector $outputFormatterCollector,
        ReportingExtensionRunner $reportingExtensionRunner,
        RectorNodeTraverser $rectorNodeTraverser,
        StubLoader $stubLoader,
        YamlProcessor $yamlProcessor,
        ChangedFilesDetector $changedFilesDetector,
        UnchangedFilesFilter $unchangedFilesFilter,
        SymfonyStyle $symfonyStyle,
        array $paths
    ) {
        $this->filesFinder = $phpFilesFinder;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorGuard = $rectorGuard;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->rectorApplication = $rectorApplication;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->reportingExtensionRunner = $reportingExtensionRunner;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->stubLoader = $stubLoader;
        $this->paths = $paths;
        $this->yamlProcessor = $yamlProcessor;
        $this->unchangedFilesFilter = $unchangedFilesFilter;

        parent::__construct();
        $this->changedFilesDetector = $changedFilesDetector;
        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(static::class));

        $this->addArgument(
            Option::SOURCE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Files or directories to be upgraded.'
        );
        $this->addOption(
            Option::OPTION_DRY_RUN,
            'n',
            InputOption::VALUE_NONE,
            'See diff of changes, do not save them to files.'
        );

        $this->addOption(
            Option::OPTION_AUTOLOAD_FILE,
            'a',
            InputOption::VALUE_REQUIRED,
            'File with extra autoload'
        );

        $this->addOption(
            Option::HIDE_AUTOLOAD_ERRORS,
            'e',
            InputOption::VALUE_NONE,
            'Hide autoload errors for the moment.'
        );

        $this->addOption(
            Option::MATCH_GIT_DIFF,
            null,
            InputOption::VALUE_NONE,
            'Execute only on file(s) matching the git diff.'
        );

        $this->addOption(
            Option::OPTION_ONLY,
            'r',
            InputOption::VALUE_REQUIRED,
            'Run only one single Rector from the loaded Rectors (in services, sets, etc).'
        );

        $availableOutputFormatters = $this->outputFormatterCollector->getNames();
        $this->addOption(
            Option::OPTION_OUTPUT_FORMAT,
            'o',
            InputOption::VALUE_OPTIONAL,
            sprintf('Select output format: "%s".', implode('", "', $availableOutputFormatters)),
            ConsoleOutputFormatter::NAME
        );

        $this->addOption(
            Option::OPTION_NO_PROGRESS_BAR,
            null,
            InputOption::VALUE_NONE,
            'Hide progress bar. Useful e.g. for nicer CI output.'
        );

        $this->addOption(
            Option::OPTION_OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Location for file to dump result in. Useful for Docker or automated processes'
        );

        $this->addOption(Option::CACHE_DEBUG, null, InputOption::VALUE_NONE, 'Debug changed file cache');
        $this->addOption(Option::OPTION_CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear un-chaged files cache');

        $this->addOption(Option::OPTION_PARALLEL, null, InputOption::VALUE_NONE, 'Experimental feature of parallel processing');
        $this->addOption(Option::OPTION_PARALLEL_PROCESSES_COUNT, null, InputOption::VALUE_REQUIRED, 'Experimental feature of parallel processing', 4);
    }

    /**
     * @return string[]
     */
    protected function resolvesSourcePaths(InputInterface $input): array
    {
        $commandLinePaths = (array) $input->getArgument(Option::SOURCE);

        // manual command line value has priority
        if (count($commandLinePaths) > 0) {
            return $commandLinePaths;
        }

        // fallback to config defined paths
        return $this->paths;
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     * @return SmartFileInfo[]
     */
    protected function processWithCache(array $phpFileInfos): array
    {
        if (! $this->configuration->isCacheEnabled()) {
            return $phpFileInfos;
        }

        // cache stuff
        if ($this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }

        if ($this->configuration->isCacheDebug()) {
            $this->symfonyStyle->note(sprintf('[cache] %d files before cache filter', count($phpFileInfos)));
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }

    protected function reportZeroCacheRectorsCondition(): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        if (! $this->rectorNodeTraverser->hasZeroCacheRectors()) {
            return;
        }

        $this->symfonyStyle->note(sprintf(
            'Ruleset contains %d rules that need "--clear-cache" option to analyse full project',
            $this->rectorNodeTraverser->getZeroCacheRectorCount()
        ));
    }

    protected function invalidateAffectedCacheFiles(): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        foreach ($this->errorAndDiffCollector->getAffectedFileInfos() as $affectedFileInfo) {
            $this->changedFilesDetector->invalidateFile($affectedFileInfo);
        }
    }
}
