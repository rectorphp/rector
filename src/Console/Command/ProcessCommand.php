<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use PHPStan\Analyser\NodeScopeResolver;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Reporting\MissingRectorRulesReporter;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\VersionBonding\Application\MissedRectorDueVersionChecker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\ShellCode;

final class ProcessCommand extends Command
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private AdditionalAutoloader $additionalAutoloader,
        private ChangedFilesDetector $changedFilesDetector,
        private OutputFormatterCollector $outputFormatterCollector,
        private MissingRectorRulesReporter $missingRectorRulesReporter,
        private ApplicationFileProcessor $applicationFileProcessor,
        private FileFactory $fileFactory,
        private BootstrapFilesIncluder $bootstrapFilesIncluder,
        private ProcessResultFactory $processResultFactory,
        private NodeScopeResolver $nodeScopeResolver,
        private DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator,
        private ConfigurationFactory $configurationFactory,
        private MissedRectorDueVersionChecker $missedRectorDueVersionChecker,
        private array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Upgrades or refactors source code with provided rectors');

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

        $names = $this->outputFormatterCollector->getNames();

        $description = sprintf('Select output format: "%s".', implode('", "', $names));
        $this->addOption(
            Option::OUTPUT_FORMAT,
            Option::OUTPUT_FORMAT_SHORT,
            InputOption::VALUE_OPTIONAL,
            $description,
            ConsoleOutputFormatter::NAME
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = $this->missingRectorRulesReporter->reportIfMissing();
        if ($exitCode !== null) {
            return $exitCode;
        }

        $configuration = $this->configurationFactory->createFromInput($input);

        // register autoloaded and included files
        $this->bootstrapFilesIncluder->includeBootstrapFiles();

        $this->additionalAutoloader->autoloadInput($input);
        $this->additionalAutoloader->autoloadPaths();

        $paths = $configuration->getPaths();

        // 0. add files and directories to static locator
        $this->dynamicSourceLocatorDecorator->addPaths($paths);

        // 1. inform user about non-runnable rules
        $this->missedRectorDueVersionChecker->check($this->rectors);

        // 2. collect all files from files+dirs provided paths
        $files = $this->fileFactory->createFromPaths($paths, $configuration);

        // 3. PHPStan has to know about all files too
        $this->configurePHPStanNodeScopeResolver($files);

        // MAIN PHASE
        // 4. run Rector
        $this->applicationFileProcessor->run($files, $configuration);

        // REPORTING PHASE
        // 5. reporting phase
        // report diffs and errors
        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);

        // here should be value obect factory
        $processResult = $this->processResultFactory->create($files);
        $outputFormatter->report($processResult, $configuration);

        // invalidate affected files
        $this->invalidateCacheChangedFiles($processResult);

        return $this->resolveReturnCode($processResult, $configuration);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $application = $this->getApplication();
        if (! $application instanceof Application) {
            throw new ShouldNotHappenException();
        }

        $optionDebug = (bool) $input->getOption(Option::DEBUG);
        if ($optionDebug) {
            $application->setCatchExceptions(false);
        }

        // clear cache
        $optionClearCache = (bool) $input->getOption(Option::CLEAR_CACHE);
        if ($optionDebug || $optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }

    private function invalidateCacheChangedFiles(ProcessResult $processResult): void
    {
        foreach ($processResult->getChangedFileInfos() as $changedFileInfo) {
            $this->changedFilesDetector->invalidateFile($changedFileInfo);
        }
    }

    private function resolveReturnCode(ProcessResult $processResult, Configuration $configuration): int
    {
        // some errors were found → fail
        if ($processResult->getErrors() !== []) {
            return ShellCode::ERROR;
        }

        // inverse error code for CI dry-run
        if (! $configuration->isDryRun()) {
            return ShellCode::SUCCESS;
        }

        return $processResult->getFileDiffs() === [] ? ShellCode::SUCCESS : ShellCode::ERROR;
    }

    /**
     * @param File[] $files
     */
    private function configurePHPStanNodeScopeResolver(array $files): void
    {
        $filePaths = [];
        foreach ($files as $file) {
            $smartFileInfo = $file->getSmartFileInfo();
            $pathName = $smartFileInfo->getPathname();

            if (\str_ends_with($pathName, '.php')) {
                $filePaths[] = $pathName;
            }
        }

        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }
}
