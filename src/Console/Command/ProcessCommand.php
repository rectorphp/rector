<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use function Amp\ByteStream\buffer;
use function Amp\call;
use Amp\Loop;
use Amp\Process\Process;
use function Amp\Promise\all;
use Generator;
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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessCommand extends AbstractCommand
{
    /**
     * @var string[]
     */
    private $paths = [];

    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var AdditionalAutoloader
     */
    private $additionalAutoloader;

    /**
     * @var RectorGuard
     */
    private $rectorGuard;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var RectorApplication
     */
    private $rectorApplication;

    /**
     * @var OutputFormatterCollector
     */
    private $outputFormatterCollector;

    /**
     * @var ReportingExtensionRunner
     */
    private $reportingExtensionRunner;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var StubLoader
     */
    private $stubLoader;

    /**
     * @var YamlProcessor
     */
    private $yamlProcessor;

    /**
     * @var UnchangedFilesFilter
     */
    private $unchangedFilesFilter;

    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var int
     */
    private const MINIMUM_FILES_TO_PROCESS_IN_PARALLEL = 1;
    /**
     * @var int
     */
    private const MAX_PROCESSES_COUNT = 4;

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
        $this->setName(CommandNaming::classToName(self::class));
        $this->setAliases(['rectify']);

        $this->setDescription('Upgrade or refactor source code with provided rectors');
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

        $this->addOption('parallel', null, InputOption::VALUE_NONE, 'Experimental feature of parallel processing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configuration->resolveFromInput($input);
        $this->configuration->setAreAnyPhpRectorsLoaded((bool) $this->rectorNodeTraverser->getPhpRectorCount());

        $this->rectorGuard->ensureSomeRectorsAreRegistered();
        $this->rectorGuard->ensureGetNodeTypesAreNodes();
        $this->stubLoader->loadStubs();

        $source = $this->resolvesSourcePaths($input);

        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $source,
            $this->configuration->getFileExtensions(),
            $this->configuration->mustMatchGitDiff()
        );

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $source);

        $phpFileInfos = $this->processWithCache($phpFileInfos);

        $this->configuration->setFileInfos($phpFileInfos);

        if ($this->configuration->isParallelEnabled()) {
            return $this->processFilesInParallel($phpFileInfos);
        }

        if ($this->configuration->isCacheDebug()) {
            $this->symfonyStyle->note(sprintf('[cache] %d files after cache filter', count($phpFileInfos)));
            $this->symfonyStyle->listing($phpFileInfos);
        }

        $this->yamlProcessor->run($source);

        $this->rectorApplication->runOnFileInfos($phpFileInfos);

        $this->reportZeroCacheRectorsCondition();

        // report diffs and errors
        $outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($this->errorAndDiffCollector);

        $this->reportingExtensionRunner->run();

        // invalidate affected files
        $this->invalidateAffectedCacheFiles();

        // some errors were found → fail
        if ($this->errorAndDiffCollector->getErrors() !== []) {
            return ShellCode::SUCCESS;
        }

        // inverse error code for CI dry-run
        if ($this->configuration->isDryRun() && $this->errorAndDiffCollector->getFileDiffsCount()) {
            return ShellCode::ERROR;
        }

        return ShellCode::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function resolvesSourcePaths(InputInterface $input): array
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
    private function processWithCache(array $phpFileInfos): array
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

    private function reportZeroCacheRectorsCondition(): void
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

    private function invalidateAffectedCacheFiles(): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        foreach ($this->errorAndDiffCollector->getAffectedFileInfos() as $affectedFileInfo) {
            $this->changedFilesDetector->invalidateFile($affectedFileInfo);
        }
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     */
    private function processFilesInParallel(array $phpFileInfos): int
    {
        /** @var string[] $fileNames */
        $fileNames = array_map(static function (SmartFileInfo $smartFileInfo) {
            return $smartFileInfo->getRelativePathname();
        }, $phpFileInfos);
        $filesToChunkCount = (int) max(
            self::MINIMUM_FILES_TO_PROCESS_IN_PARALLEL,
            (int) (count($phpFileInfos) / self::MAX_PROCESSES_COUNT)
        );
        $chunkedFilenames = array_chunk($fileNames, $filesToChunkCount);

        $results = [];

        Loop::run(static function () use (&$results, $chunkedFilenames) {
            $promises = [];

            foreach ($chunkedFilenames as $filenamesForChildProcess) {
                $promises[] = call(function () use ($filenamesForChildProcess): Generator {
                    $script = array_merge(
                        [
                            __DIR__ . '/../../../bin/rector',
                            CommandNaming::classToName(ProcessWorkerCommand::class),
                            // Please can we take somehow everything from configuration object or input object?
                            // etc $this->configuration->toCliArguments() ??
                            '--dry-run',
                        ],
                        // TODO: Does worker process accept multiple files or only source directories?
                        $filenamesForChildProcess
                    );

                    $process = new Process($script);

                    yield $process->start();

                    return yield buffer($process->getStdout());
                });
            }

            $results = yield all($promises);
        });

        print_r($results);

        return ShellCode::SUCCESS;
    }
}
