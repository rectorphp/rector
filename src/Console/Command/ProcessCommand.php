<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use PHPStan\Analyser\NodeScopeResolver;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\FileSystem\PhpFilesFinder;
use Rector\Core\Reporting\MissingRectorRulesReporter;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObjectFactory\Application\FileFactory;
use Rector\Core\ValueObjectFactory\ProcessResultFactory;
use RectorPrefix20210514\Symfony\Component\Console\Application;
use RectorPrefix20210514\Symfony\Component\Console\Command\Command;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ProcessCommand extends \RectorPrefix20210514\Symfony\Component\Console\Command\Command
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
     * @var \Rector\Core\Configuration\Configuration
     */
    private $configuration;
    /**
     * @var \Rector\Core\Console\Output\OutputFormatterCollector
     */
    private $outputFormatterCollector;
    /**
     * @var \Rector\Core\FileSystem\PhpFilesFinder
     */
    private $phpFilesFinder;
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
    public function __construct(\Rector\Core\Autoloading\AdditionalAutoloader $additionalAutoloader, \Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Core\Configuration\Configuration $configuration, \Rector\Core\Console\Output\OutputFormatterCollector $outputFormatterCollector, \Rector\Core\FileSystem\PhpFilesFinder $phpFilesFinder, \Rector\Core\Reporting\MissingRectorRulesReporter $missingRectorRulesReporter, \Rector\Core\Application\ApplicationFileProcessor $applicationFileProcessor, \Rector\Core\ValueObjectFactory\Application\FileFactory $fileFactory, \Rector\Core\Autoloading\BootstrapFilesIncluder $bootstrapFilesIncluder, \Rector\Core\ValueObjectFactory\ProcessResultFactory $processResultFactory, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator)
    {
        $this->additionalAutoloader = $additionalAutoloader;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->configuration = $configuration;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->phpFilesFinder = $phpFilesFinder;
        $this->missingRectorRulesReporter = $missingRectorRulesReporter;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->fileFactory = $fileFactory;
        $this->bootstrapFilesIncluder = $bootstrapFilesIncluder;
        $this->processResultFactory = $processResultFactory;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Upgrade or refactor source code with provided rectors');
        $this->addArgument(\Rector\Core\Configuration\Option::SOURCE, \RectorPrefix20210514\Symfony\Component\Console\Input\InputArgument::OPTIONAL | \RectorPrefix20210514\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'Files or directories to be upgraded.');
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_DRY_RUN, 'n', \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'See diff of changes, do not save them to files.');
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_AUTOLOAD_FILE, 'a', \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'File with extra autoload');
        $names = $this->outputFormatterCollector->getNames();
        $description = \sprintf('Select output format: "%s".', \implode('", "', $names));
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FORMAT, 'o', \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, $description, \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME);
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_NO_PROGRESS_BAR, null, \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Hide progress bar. Useful e.g. for nicer CI output.');
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_NO_DIFFS, null, \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Hide diffs of changed files. Useful e.g. for nicer CI output.');
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FILE, null, \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Location for file to dump result in. Useful for Docker or automated processes');
        $this->addOption(\Rector\Core\Configuration\Option::CACHE_DEBUG, null, \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Debug changed file cache');
        $this->addOption(\Rector\Core\Configuration\Option::OPTION_CLEAR_CACHE, null, \RectorPrefix20210514\Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Clear unchaged files cache');
    }
    protected function execute(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $exitCode = $this->missingRectorRulesReporter->reportIfMissing();
        if ($exitCode !== null) {
            return $exitCode;
        }
        $this->configuration->resolveFromInput($input);
        $this->configuration->validateConfigParameters();
        $paths = $this->configuration->getPaths();
        $phpFileInfos = $this->phpFilesFinder->findInPaths($paths);
        // register autoloaded and included files
        $this->bootstrapFilesIncluder->includeBootstrapFiles();
        $this->additionalAutoloader->autoloadInput($input);
        $this->additionalAutoloader->autoloadPaths();
        // PHPStan has to know about all files!
        $this->configurePHPStanNodeScopeResolver($phpFileInfos);
        // 0. add files and directories to static locator
        $this->dynamicSourceLocatorDecorator->addPaths($paths);
        $files = $this->fileFactory->createFromPaths($paths);
        $this->applicationFileProcessor->run($files);
        // report diffs and errors
        $outputFormat = (string) $input->getOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FORMAT);
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        // here should be value obect factory
        $processResult = $this->processResultFactory->create($files);
        $outputFormatter->report($processResult);
        // invalidate affected files
        $this->invalidateCacheChangedFiles($processResult);
        return $this->resolveReturnCode($processResult);
    }
    protected function initialize(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface $output) : void
    {
        $application = $this->getApplication();
        if (!$application instanceof \RectorPrefix20210514\Symfony\Component\Console\Application) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $optionDebug = (bool) $input->getOption(\Rector\Core\Configuration\Option::OPTION_DEBUG);
        if ($optionDebug) {
            $application->setCatchExceptions(\false);
            // clear cache
            $this->changedFilesDetector->clear();
            return;
        }
        // clear cache
        $optionClearCache = (bool) $input->getOption(\Rector\Core\Configuration\Option::OPTION_CLEAR_CACHE);
        if ($optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }
    private function invalidateCacheChangedFiles(\Rector\Core\ValueObject\ProcessResult $processResult) : void
    {
        if (!$this->configuration->isCacheEnabled()) {
            return;
        }
        foreach ($processResult->getChangedFileInfos() as $changedFileInfo) {
            $this->changedFilesDetector->invalidateFile($changedFileInfo);
        }
    }
    private function resolveReturnCode(\Rector\Core\ValueObject\ProcessResult $processResult) : int
    {
        // some errors were found → fail
        if ($processResult->getErrors() !== []) {
            return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::ERROR;
        }
        // inverse error code for CI dry-run
        if (!$this->configuration->isDryRun()) {
            return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::SUCCESS;
        }
        return $processResult->getFileDiffs() === [] ? \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::SUCCESS : \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::ERROR;
    }
    /**
     * @param SmartFileInfo[] $fileInfos
     */
    private function configurePHPStanNodeScopeResolver(array $fileInfos) : void
    {
        $filePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $filePaths[] = $fileInfo->getPathname();
        }
        $this->nodeScopeResolver->setAnalysedFiles($filePaths);
    }
}
