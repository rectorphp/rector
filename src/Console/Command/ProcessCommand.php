<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use Rector\Application\ApplicationFileProcessor;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Configuration\ConfigInitializer;
use Rector\Configuration\ConfigurationFactory;
use Rector\Configuration\Option;
use Rector\Console\ExitCode;
use Rector\Console\Output\OutputFormatterCollector;
use Rector\Console\ProcessConfigureDecorator;
use Rector\Exception\ShouldNotHappenException;
use Rector\Reporting\DeprecatedRulesReporter;
use Rector\Reporting\MissConfigurationReporter;
use Rector\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Util\MemoryLimiter;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
use RectorPrefix202411\Symfony\Component\Console\Application;
use RectorPrefix202411\Symfony\Component\Console\Command\Command;
use RectorPrefix202411\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202411\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202411\Symfony\Component\Console\Style\SymfonyStyle;
final class ProcessCommand extends Command
{
    /**
     * @readonly
     * @var \Rector\Autoloading\AdditionalAutoloader
     */
    private $additionalAutoloader;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\Configuration\ConfigInitializer
     */
    private $configInitializer;
    /**
     * @readonly
     * @var \Rector\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @readonly
     * @var \Rector\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\Console\Output\OutputFormatterCollector
     */
    private $outputFormatterCollector;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\Util\MemoryLimiter
     */
    private $memoryLimiter;
    /**
     * @readonly
     * @var \Rector\Configuration\ConfigurationFactory
     */
    private $configurationFactory;
    /**
     * @readonly
     * @var \Rector\Reporting\DeprecatedRulesReporter
     */
    private $deprecatedRulesReporter;
    /**
     * @readonly
     * @var \Rector\Reporting\MissConfigurationReporter
     */
    private $missConfigurationReporter;
    public function __construct(AdditionalAutoloader $additionalAutoloader, ChangedFilesDetector $changedFilesDetector, ConfigInitializer $configInitializer, ApplicationFileProcessor $applicationFileProcessor, DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, OutputFormatterCollector $outputFormatterCollector, SymfonyStyle $symfonyStyle, MemoryLimiter $memoryLimiter, ConfigurationFactory $configurationFactory, DeprecatedRulesReporter $deprecatedRulesReporter, MissConfigurationReporter $missConfigurationReporter)
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
        $this->deprecatedRulesReporter = $deprecatedRulesReporter;
        $this->missConfigurationReporter = $missConfigurationReporter;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('process');
        $this->setDescription('Upgrades or refactors source code with provided rectors');
        $this->setHelp(<<<'EOF'
The <info>%command.name%</info> command will run Rector main feature:

  <info>%command.full_name%</info>

To specify a folder or a file, you can run:

  <info>%command.full_name% src/Controller</info>

You can also dry run to see the changes that Rector will make with the <comment>--dry-run</comment> option:

  <info>%command.full_name% src/Controller --dry-run</info>

It's also possible to get debug via the <comment>--debug</comment> option:

  <info>%command.full_name% src/Controller --dry-run --debug</info>
EOF
);
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
            // read from rector.php, no paths definition needs withPaths() config
            if ($paths === []) {
                $this->symfonyStyle->error('No paths definition in rector configuration, define paths: https://getrector.com/documentation/define-paths');
                return ExitCode::FAILURE;
            }
            // read from cli paths arguments, eg: vendor/bin/rector process A B C which A, B, and C not exists
            $isSingular = \count($paths) === 1;
            $this->symfonyStyle->error(\sprintf('The following given path%s do%s not match any file%s or director%s: %s%s', $isSingular ? '' : 's', $isSingular ? 'es' : '', $isSingular ? '' : 's', $isSingular ? 'y' : 'ies', \PHP_EOL . \PHP_EOL . ' - ', \implode(\PHP_EOL . ' - ', $paths)));
            return ExitCode::FAILURE;
        }
        // MAIN PHASE
        // 2. run Rector
        $processResult = $this->applicationFileProcessor->run($configuration, $input);
        // REPORTING PHASE
        // 3. reporting phaseRunning 2nd time with collectors data
        // report diffs and errors
        $outputFormat = $configuration->getOutputFormat();
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($processResult, $configuration);
        $this->deprecatedRulesReporter->reportDeprecatedRules();
        $this->deprecatedRulesReporter->reportDeprecatedSkippedRules();
        $this->missConfigurationReporter->reportSkippedNeverRegisteredRules();
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
