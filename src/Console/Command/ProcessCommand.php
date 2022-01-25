<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Caching\Detector\ChangedFilesDetector;
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
use Rector\Core\Util\MemoryLimiter;
use Rector\Core\Validation\EmptyConfigurableRectorChecker;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\VersionBonding\Application\MissedRectorDueVersionChecker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

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
        private readonly BootstrapFilesIncluder $bootstrapFilesIncluder,
        private readonly ProcessResultFactory $processResultFactory,
        private readonly DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator,
        private readonly MissedRectorDueVersionChecker $missedRectorDueVersionChecker,
        private readonly EmptyConfigurableRectorChecker $emptyConfigurableRectorChecker,
        private readonly OutputFormatterCollector $outputFormatterCollector,
        private readonly SymfonyStyle $symfonyStyle,
        private readonly MemoryLimiter $memoryLimiter,
        private readonly array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Upgrades or refactors source code with provided rectors');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exitCode = $this->missingRectorRulesReporter->reportIfMissing();
        if ($exitCode !== null) {
            return $exitCode;
        }

        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimiter->adjust($configuration);

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
        $this->emptyConfigurableRectorChecker->check();

        // MAIN PHASE
        // 3. run Rector
        $systemErrorsAndFileDiffs = $this->applicationFileProcessor->run($configuration, $input);

        // REPORTING PHASE
        // 4. reporting phase
        // report diffs and errors
        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);

        $processResult = $this->processResultFactory->create($systemErrorsAndFileDiffs);
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
        // some system errors were found → fail
        if ($processResult->getErrors() !== []) {
            return Command::FAILURE;
        }

        // inverse error code for CI dry-run
        if (! $configuration->isDryRun()) {
            return Command::SUCCESS;
        }

        return $processResult->getFileDiffs() === [] ? Command::SUCCESS : Command::FAILURE;
    }
}
