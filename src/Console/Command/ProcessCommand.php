<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Console\Command;

use RectorPrefix20220606\Rector\Caching\Detector\ChangedFilesDetector;
use RectorPrefix20220606\Rector\ChangesReporting\Output\JsonOutputFormatter;
use RectorPrefix20220606\Rector\Core\Application\ApplicationFileProcessor;
use RectorPrefix20220606\Rector\Core\Autoloading\AdditionalAutoloader;
use RectorPrefix20220606\Rector\Core\Configuration\Option;
use RectorPrefix20220606\Rector\Core\Console\Output\OutputFormatterCollector;
use RectorPrefix20220606\Rector\Core\Contract\Console\OutputStyleInterface;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Reporting\MissingRectorRulesReporter;
use RectorPrefix20220606\Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use RectorPrefix20220606\Rector\Core\Util\MemoryLimiter;
use RectorPrefix20220606\Rector\Core\Validation\EmptyConfigurableRectorChecker;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\ProcessResult;
use RectorPrefix20220606\Rector\Core\ValueObjectFactory\ProcessResultFactory;
use RectorPrefix20220606\Symfony\Component\Console\Application;
use RectorPrefix20220606\Symfony\Component\Console\Command\Command;
use RectorPrefix20220606\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220606\Symfony\Component\Console\Output\OutputInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ProcessCommand extends AbstractProcessCommand
{
    /**
     * @readonly
     * @var \Rector\Core\Autoloading\AdditionalAutoloader
     */
    private $additionalAutoloader;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\Core\Reporting\MissingRectorRulesReporter
     */
    private $missingRectorRulesReporter;
    /**
     * @readonly
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @readonly
     * @var \Rector\Core\ValueObjectFactory\ProcessResultFactory
     */
    private $processResultFactory;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\Core\Validation\EmptyConfigurableRectorChecker
     */
    private $emptyConfigurableRectorChecker;
    /**
     * @readonly
     * @var \Rector\Core\Console\Output\OutputFormatterCollector
     */
    private $outputFormatterCollector;
    /**
     * @readonly
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $rectorOutputStyle;
    /**
     * @readonly
     * @var \Rector\Core\Util\MemoryLimiter
     */
    private $memoryLimiter;
    public function __construct(AdditionalAutoloader $additionalAutoloader, ChangedFilesDetector $changedFilesDetector, MissingRectorRulesReporter $missingRectorRulesReporter, ApplicationFileProcessor $applicationFileProcessor, ProcessResultFactory $processResultFactory, DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, EmptyConfigurableRectorChecker $emptyConfigurableRectorChecker, OutputFormatterCollector $outputFormatterCollector, OutputStyleInterface $rectorOutputStyle, MemoryLimiter $memoryLimiter)
    {
        $this->additionalAutoloader = $additionalAutoloader;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->missingRectorRulesReporter = $missingRectorRulesReporter;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->processResultFactory = $processResultFactory;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->emptyConfigurableRectorChecker = $emptyConfigurableRectorChecker;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->memoryLimiter = $memoryLimiter;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('process');
        $this->setDescription('Upgrades or refactors source code with provided rectors');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $exitCode = $this->missingRectorRulesReporter->reportIfMissing();
        if ($exitCode !== null) {
            return $exitCode;
        }
        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimiter->adjust($configuration);
        // disable console output in case of json output formatter
        if ($configuration->getOutputFormat() === JsonOutputFormatter::NAME) {
            $this->rectorOutputStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        $this->additionalAutoloader->autoloadInput($input);
        $this->additionalAutoloader->autoloadPaths();
        $paths = $configuration->getPaths();
        // 1. add files and directories to static locator
        $this->dynamicSourceLocatorDecorator->addPaths($paths);
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
        $this->invalidateCacheForChangedAndErroredFiles($processResult);
        return $this->resolveReturnCode($processResult, $configuration);
    }
    protected function initialize(InputInterface $input, OutputInterface $output) : void
    {
        $application = $this->getApplication();
        if (!$application instanceof Application) {
            throw new ShouldNotHappenException();
        }
        $optionDebug = (bool) $input->getOption(Option::DEBUG);
        if ($optionDebug) {
            $application->setCatchExceptions(\false);
        }
        // clear cache
        $optionClearCache = (bool) $input->getOption(Option::CLEAR_CACHE);
        if ($optionDebug || $optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }
    private function invalidateCacheForChangedAndErroredFiles(ProcessResult $processResult) : void
    {
        foreach ($processResult->getChangedFileInfos() as $changedFileInfo) {
            $this->changedFilesDetector->invalidateFile($changedFileInfo);
        }
        foreach ($processResult->getErrors() as $systemError) {
            $errorFile = $systemError->getFile();
            if (!\is_string($errorFile)) {
                continue;
            }
            $errorFileInfo = new SmartFileInfo($errorFile);
            $this->changedFilesDetector->invalidateFile($errorFileInfo);
        }
    }
    private function resolveReturnCode(ProcessResult $processResult, Configuration $configuration) : int
    {
        // some system errors were found → fail
        if ($processResult->getErrors() !== []) {
            return Command::FAILURE;
        }
        // inverse error code for CI dry-run
        if (!$configuration->isDryRun()) {
            return Command::SUCCESS;
        }
        return $processResult->getFileDiffs() === [] ? Command::SUCCESS : Command::FAILURE;
    }
}
