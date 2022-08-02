<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Reporting\MissingRectorRulesReporter;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\Util\MemoryLimiter;
use Rector\Core\Validation\EmptyConfigurableRectorChecker;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use RectorPrefix202208\Symfony\Component\Console\Application;
use RectorPrefix202208\Symfony\Component\Console\Command\Command;
use RectorPrefix202208\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202208\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
final class ProcessCommand extends \Rector\Core\Console\Command\AbstractProcessCommand
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
