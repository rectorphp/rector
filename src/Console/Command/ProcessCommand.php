<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Configuration\ConfigInitializer;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\ExitCode;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Console\ProcessConfigureDecorator;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\Util\MemoryLimiter;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use RectorPrefix202312\Symfony\Component\Console\Application;
use RectorPrefix202312\Symfony\Component\Console\Command\Command;
use RectorPrefix202312\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202312\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202312\Symfony\Component\Console\Style\SymfonyStyle;
final class ProcessCommand extends Command
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
     * @var \Rector\Core\Configuration\ConfigInitializer
     */
    private $configInitializer;
    /**
     * @readonly
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\Core\Console\Output\OutputFormatterCollector
     */
    private $outputFormatterCollector;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\Core\Util\MemoryLimiter
     */
    private $memoryLimiter;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\ConfigurationFactory
     */
    private $configurationFactory;
    public function __construct(AdditionalAutoloader $additionalAutoloader, ChangedFilesDetector $changedFilesDetector, ConfigInitializer $configInitializer, ApplicationFileProcessor $applicationFileProcessor, DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, OutputFormatterCollector $outputFormatterCollector, SymfonyStyle $symfonyStyle, MemoryLimiter $memoryLimiter, ConfigurationFactory $configurationFactory)
    {
        $this->additionalAutoloader = $additionalAutoloader;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->configInitializer = $configInitializer;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->symfonyStyle = $symfonyStyle;
        $this->memoryLimiter = $memoryLimiter;
        $this->configurationFactory = $configurationFactory;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('process');
        $this->setDescription('Upgrades or refactors source code with provided rectors');
        ProcessConfigureDecorator::decorate($this);
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // missing config? add it :)
        if (!$this->configInitializer->areSomeRectorsLoaded()) {
            $this->configInitializer->createConfig(\getcwd());
            return self::SUCCESS;
        }
        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimiter->adjust($configuration);
        // disable console output in case of json output formatter
        if ($configuration->getOutputFormat() === JsonOutputFormatter::NAME) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        $this->additionalAutoloader->autoloadInput($input);
        $this->additionalAutoloader->autoloadPaths();
        $paths = $configuration->getPaths();
        // 1. add files and directories to static locator
        $this->dynamicSourceLocatorDecorator->addPaths($paths);
        if ($this->dynamicSourceLocatorDecorator->isPathsEmpty()) {
            $this->symfonyStyle->error('The given paths do not match any files');
            return ExitCode::FAILURE;
        }
        // MAIN PHASE
        // 2. run Rector
        $processResult = $this->applicationFileProcessor->run($configuration, $input);
        // 3. collectors phase
        if ($processResult->getCollectedData() !== []) {
            $this->symfonyStyle->newLine(2);
            $this->symfonyStyle->title('Running 2nd time with collectors data');
            $configuration->setCollectedData($processResult->getCollectedData());
            $configuration->enableSecondRun();
            // reset rules in Rector traverser
            $nextProcessResult = $this->applicationFileProcessor->run($configuration, $input);
            // @todo merge results here
            $this->symfonyStyle->newLine(3);
            // unset all rectors that are not collector
            // set new collector rectors - have a custom tag? yes
        }
        // REPORTING PHASE
        // 4. reporting phaseRunning 2nd time with collectors data
        // report diffs and errors
        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($processResult, $configuration);
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
    /**
     * @return ExitCode::*
     */
    private function resolveReturnCode(ProcessResult $processResult, Configuration $configuration) : int
    {
        // some system errors were found → fail
        if ($processResult->getSystemErrors() !== []) {
            return ExitCode::FAILURE;
        }
        // inverse error code for CI dry-run
        if (!$configuration->isDryRun()) {
            return ExitCode::SUCCESS;
        }
        if ($processResult->getFileDiffs() !== []) {
            return ExitCode::CHANGED_CODE;
        }
        return ExitCode::SUCCESS;
    }
}
