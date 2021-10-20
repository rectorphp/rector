<?php

declare (strict_types=1);
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
use Rector\Core\Validation\EmptyConfigurableRectorChecker;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use Rector\VersionBonding\Application\MissedRectorDueVersionChecker;
use RectorPrefix20211020\Symfony\Component\Console\Application;
use RectorPrefix20211020\Symfony\Component\Console\Command\Command;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20211020\Symfony\Component\Console\Output\OutputInterface;
final class ProcessCommand extends \RectorPrefix20211020\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Rector\Core\Autoloading\AdditionalAutoloader
     */
    private $additionalAutoloader;
    /**
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var \Rector\Core\Console\Output\OutputFormatterCollector
     */
    private $outputFormatterCollector;
    /**
     * @var \Rector\Core\Reporting\MissingRectorRulesReporter
     */
    private $missingRectorRulesReporter;
    /**
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @var \Rector\Core\ValueObjectFactory\Application\FileFactory
     */
    private $fileFactory;
    /**
     * @var \Rector\Core\Autoloading\BootstrapFilesIncluder
     */
    private $bootstrapFilesIncluder;
    /**
     * @var \Rector\Core\ValueObjectFactory\ProcessResultFactory
     */
    private $processResultFactory;
    /**
     * @var \PHPStan\Analyser\NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @var \Rector\Core\Configuration\ConfigurationFactory
     */
    private $configurationFactory;
    /**
     * @var \Rector\VersionBonding\Application\MissedRectorDueVersionChecker
     */
    private $missedRectorDueVersionChecker;
    /**
     * @var \Rector\Core\Validation\EmptyConfigurableRectorChecker
     */
    private $emptyConfigurableRectorChecker;
    /**
     * @var \Rector\Core\Contract\Rector\RectorInterface[]
     */
    private $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(\Rector\Core\Autoloading\AdditionalAutoloader $additionalAutoloader, \Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Core\Console\Output\OutputFormatterCollector $outputFormatterCollector, \Rector\Core\Reporting\MissingRectorRulesReporter $missingRectorRulesReporter, \Rector\Core\Application\ApplicationFileProcessor $applicationFileProcessor, \Rector\Core\ValueObjectFactory\Application\FileFactory $fileFactory, \Rector\Core\Autoloading\BootstrapFilesIncluder $bootstrapFilesIncluder, \Rector\Core\ValueObjectFactory\ProcessResultFactory $processResultFactory, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, \Rector\Core\Configuration\ConfigurationFactory $configurationFactory, \Rector\VersionBonding\Application\MissedRectorDueVersionChecker $missedRectorDueVersionChecker, \Rector\Core\Validation\EmptyConfigurableRectorChecker $emptyConfigurableRectorChecker, array $rectors)
    {
        $this->additionalAutoloader = $additionalAutoloader;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->missingRectorRulesReporter = $missingRectorRulesReporter;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->fileFactory = $fileFactory;
        $this->bootstrapFilesIncluder = $bootstrapFilesIncluder;
        $this->processResultFactory = $processResultFactory;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->configurationFactory = $configurationFactory;
        $this->missedRectorDueVersionChecker = $missedRectorDueVersionChecker;
        $this->emptyConfigurableRectorChecker = $emptyConfigurableRectorChecker;
        $this->rectors = $rectors;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Upgrades or refactors source code with provided rectors');
        $this->addArgument(\Rector\Core\Configuration\Option::SOURCE, \RectorPrefix20211020\Symfony\Component\Console\Input\InputArgument::OPTIONAL | \RectorPrefix20211020\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'Files or directories to be upgraded.');
        $this->addOption(\Rector\Core\Configuration\Option::DRY_RUN, \Rector\Core\Configuration\Option::DRY_RUN_SHORT, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Only see the diff of changes, do not save them to files.');
        $this->addOption(\Rector\Core\Configuration\Option::AUTOLOAD_FILE, \Rector\Core\Configuration\Option::AUTOLOAD_FILE_SHORT, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to file with extra autoload (will be included)');
        $names = $this->outputFormatterCollector->getNames();
        $description = \sprintf('Select output format: "%s".', \implode('", "', $names));
        $this->addOption(\Rector\Core\Configuration\Option::OUTPUT_FORMAT, \Rector\Core\Configuration\Option::OUTPUT_FORMAT_SHORT, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, $description, \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME);
        $this->addOption(\Rector\Core\Configuration\Option::NO_PROGRESS_BAR, null, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Hide progress bar. Useful e.g. for nicer CI output.');
        $this->addOption(\Rector\Core\Configuration\Option::NO_DIFFS, null, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Hide diffs of changed files. Useful e.g. for nicer CI output.');
        $this->addOption(\Rector\Core\Configuration\Option::CLEAR_CACHE, null, \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Clear unchaged files cache');
    }
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output) : int
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
        // here should be value obect factory
        $processResult = $this->processResultFactory->create($files);
        $outputFormatter->report($processResult, $configuration);
        // invalidate affected files
        $this->invalidateCacheChangedFiles($processResult);
        return $this->resolveReturnCode($processResult, $configuration);
    }
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize($input, $output) : void
    {
        $application = $this->getApplication();
        if (!$application instanceof \RectorPrefix20211020\Symfony\Component\Console\Application) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $optionDebug = (bool) $input->getOption(\Rector\Core\Configuration\Option::DEBUG);
        if ($optionDebug) {
            $application->setCatchExceptions(\false);
        }
        // clear cache
        $optionClearCache = (bool) $input->getOption(\Rector\Core\Configuration\Option::CLEAR_CACHE);
        if ($optionDebug || $optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }
    private function invalidateCacheChangedFiles(\Rector\Core\ValueObject\ProcessResult $processResult) : void
    {
        foreach ($processResult->getChangedFileInfos() as $changedFileInfo) {
            $this->changedFilesDetector->invalidateFile($changedFileInfo);
        }
    }
    private function resolveReturnCode(\Rector\Core\ValueObject\ProcessResult $processResult, \Rector\Core\ValueObject\Configuration $configuration) : int
    {
        // some errors were found → fail
        if ($processResult->getErrors() !== []) {
            return \RectorPrefix20211020\Symfony\Component\Console\Command\Command::FAILURE;
        }
        // inverse error code for CI dry-run
        if (!$configuration->isDryRun()) {
            return \RectorPrefix20211020\Symfony\Component\Console\Command\Command::SUCCESS;
        }
        return $processResult->getFileDiffs() === [] ? \RectorPrefix20211020\Symfony\Component\Console\Command\Command::SUCCESS : \RectorPrefix20211020\Symfony\Component\Console\Command\Command::FAILURE;
    }
    /**
     * @param File[] $files
     */
    private function configurePHPStanNodeScopeResolver(array $files) : void
    {
        $filePaths = [];
        foreach ($files as $file) {
            $smartFileInfo = $file->getSmartFileInfo();
            $pathName = $smartFileInfo->getPathname();
            if (\substr_compare($pathName, '.php', -\strlen('.php')) === 0) {
                $filePaths[] = $pathName;
            }
        }
        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }
}
