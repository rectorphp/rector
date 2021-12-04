<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use PHPStan\Analyser\NodeScopeResolver;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Reporting\MissingRectorRulesReporter;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\Validation\EmptyConfigurableRectorChecker;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\VersionBonding\Application\MissedRectorDueVersionChecker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProcessCommand extends AbstractProcessCommand
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private readonly AdditionalAutoloader $additionalAutoloader,
        private readonly ChangedFilesDetector $changedFilesDetector,
        private readonly MissingRectorRulesReporter $missingRectorRulesReporter,
        private readonly ApplicationFileProcessor $applicationFileProcessor,
        private readonly FileFactory $fileFactory,
        private readonly BootstrapFilesIncluder $bootstrapFilesIncluder,
        private readonly ProcessResultFactory $processResultFactory,
        private readonly NodeScopeResolver $nodeScopeResolver,
        private readonly DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator,
        private readonly MissedRectorDueVersionChecker $missedRectorDueVersionChecker,
        private readonly EmptyConfigurableRectorChecker $emptyConfigurableRectorChecker,
        private readonly OutputFormatterCollector $outputFormatterCollector,
        private readonly SymfonyStyle $symfonyStyle,
        private readonly array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Upgrades or refactors source code with provided rectors');

        $names = $this->outputFormatterCollector->getNames();

        $description = sprintf('Select output format: "%s".', implode('", "', $names));
        $this->addOption(
            Option::OUTPUT_FORMAT,
            Option::OUTPUT_FORMAT_SHORT,
            InputOption::VALUE_OPTIONAL,
            $description,
            ConsoleOutputFormatter::NAME
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = $this->missingRectorRulesReporter->reportIfMissing();
        if ($exitCode !== null) {
            return $exitCode;
        }

        $configuration = $this->configurationFactory->createFromInput($input);

        // disable console output in case of json output formatter
        if ($configuration->getOutputFormat() === JsonOutputFormatter::NAME) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        // register autoloaded and included files
        $this->bootstrapFilesIncluder->includeBootstrapFiles();

        $this->additionalAutoloader->autoloadInput($input);
        $this->additionalAutoloader->autoloadPaths();

        $paths = $configuration->getPaths();

        // 0. add files and directories to static locator
        $this->dynamicSourceLocatorDecorator->addPaths($paths);

        // 1. inform user about non-runnable rules
        $this->missedRectorDueVersionChecker->check($this->rectors);

        // 2. inform user about registering configurable rule without configuration
        $this->emptyConfigurableRectorChecker->check($this->rectors);

        // 3. collect all files from files+dirs provided paths
        $files = $this->fileFactory->createFromPaths($paths, $configuration);

        // 4. PHPStan has to know about all files too
        $this->configurePHPStanNodeScopeResolver($files);

        // MAIN PHASE
        // 5. run Rector
        $this->applicationFileProcessor->run($files, $configuration);

        // REPORTING PHASE
        // 6. reporting phase
        // report diffs and errors
        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);

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
            return Command::FAILURE;
        }

        // inverse error code for CI dry-run
        if (! $configuration->isDryRun()) {
            return Command::SUCCESS;
        }

        return $processResult->getFileDiffs() === [] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @param File[] $files
     */
    private function configurePHPStanNodeScopeResolver(array $files): void
    {
        $filePaths = $this->resolvePhpFilePaths($files);
        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }

    /**
     * @param File[] $files
     * @return string[]
     */
    private function resolvePhpFilePaths(array $files): array
    {
        $filePaths = [];

        foreach ($files as $file) {
            $smartFileInfo = $file->getSmartFileInfo();
            $pathName = $smartFileInfo->getPathname();

            if (\str_ends_with($pathName, '.php')) {
                $filePaths[] = $pathName;
            }
        }

        return $filePaths;
    }
}
