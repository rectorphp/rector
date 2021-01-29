<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Nette\Utils\Strings;
use Rector\Caching\Application\CachedFileInfoFilterAndReporter;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Composer\Processor\ComposerProcessor;
use Rector\Core\Application\RectorApplication;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\Guard\RectorGuard;
use Rector\Core\NonPhpFile\NonPhpFileProcessor;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessCommand extends AbstractCommand
{
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
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var StubLoader
     */
    private $stubLoader;

    /**
     * @var NonPhpFileProcessor
     */
    private $nonPhpFileProcessor;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var CachedFileInfoFilterAndReporter
     */
    private $cachedFileInfoFilterAndReporter;

    /**
     * @var ComposerProcessor
     */
    private $composerProcessor;

    public function __construct(
        AdditionalAutoloader $additionalAutoloader,
        ChangedFilesDetector $changedFilesDetector,
        Configuration $configuration,
        ErrorAndDiffCollector $errorAndDiffCollector,
        FilesFinder $phpFilesFinder,
        NonPhpFileProcessor $nonPhpFileProcessor,
        OutputFormatterCollector $outputFormatterCollector,
        RectorApplication $rectorApplication,
        RectorGuard $rectorGuard,
        RectorNodeTraverser $rectorNodeTraverser,
        StubLoader $stubLoader,
        SymfonyStyle $symfonyStyle,
        CachedFileInfoFilterAndReporter $cachedFileInfoFilterAndReporter,
        ComposerProcessor $composerProcessor
    ) {
        $this->filesFinder = $phpFilesFinder;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorGuard = $rectorGuard;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->rectorApplication = $rectorApplication;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->stubLoader = $stubLoader;
        $this->nonPhpFileProcessor = $nonPhpFileProcessor;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->symfonyStyle = $symfonyStyle;
        $this->cachedFileInfoFilterAndReporter = $cachedFileInfoFilterAndReporter;
        $this->composerProcessor = $composerProcessor;

        parent::__construct();
    }

    protected function configure(): void
    {
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
            Option::MATCH_GIT_DIFF,
            null,
            InputOption::VALUE_NONE,
            'Execute only on file(s) matching the git diff.'
        );

        $names = $this->outputFormatterCollector->getNames();

        $description = sprintf('Select output format: "%s".', implode('", "', $names));
        $this->addOption(
            Option::OPTION_OUTPUT_FORMAT,
            'o',
            InputOption::VALUE_OPTIONAL,
            $description,
            ConsoleOutputFormatter::NAME
        );

        $this->addOption(
            Option::OPTION_NO_PROGRESS_BAR,
            null,
            InputOption::VALUE_NONE,
            'Hide progress bar. Useful e.g. for nicer CI output.'
        );

        $this->addOption(
            Option::OPTION_NO_DIFFS,
            null,
            InputOption::VALUE_NONE,
            'Hide diffs of changed files. Useful e.g. for nicer CI output.'
        );

        $this->addOption(
            Option::OPTION_OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Location for file to dump result in. Useful for Docker or automated processes'
        );

        $this->addOption(Option::CACHE_DEBUG, null, InputOption::VALUE_NONE, 'Debug changed file cache');
        $this->addOption(Option::OPTION_CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear unchaged files cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configuration->resolveFromInput($input);
        $this->configuration->validateConfigParameters();
        $this->configuration->setAreAnyPhpRectorsLoaded((bool) $this->rectorNodeTraverser->getPhpRectorCount());

        $this->rectorGuard->ensureSomeRectorsAreRegistered();
        $this->stubLoader->loadStubs();

        $paths = $this->configuration->getPaths();

        $phpFileInfos = $this->findPhpFileInfos($paths);

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $paths);

        if ($this->configuration->isCacheDebug()) {
            $message = sprintf('[cache] %d files after cache filter', count($phpFileInfos));
            $this->symfonyStyle->note($message);
            $this->symfonyStyle->listing($phpFileInfos);
        }

        $this->configuration->setFileInfos($phpFileInfos);
        $this->rectorApplication->runOnFileInfos($phpFileInfos);

        // must run after PHP rectors, because they might change class names, and these class names must be changed in configs
        $nonPhpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $paths,
            StaticNonPhpFileSuffixes::SUFFIXES
        );

        $this->nonPhpFileProcessor->runOnFileInfos($nonPhpFileInfos);

        $composerJsonFilePath = getcwd() . '/composer.json';
        $this->composerProcessor->process($composerJsonFilePath);

        $this->reportZeroCacheRectorsCondition();

        // report diffs and errors
        $outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);

        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($this->errorAndDiffCollector);

        // invalidate affected files
        $this->invalidateAffectedCacheFiles();

        // some errors were found → fail
        if ($this->errorAndDiffCollector->getErrors() !== []) {
            return ShellCode::ERROR;
        }

        // inverse error code for CI dry-run
        if ($this->configuration->isDryRun() && $this->errorAndDiffCollector->getFileDiffsCount()) {
            return ShellCode::ERROR;
        }

        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    private function findPhpFileInfos(array $paths): array
    {
        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $paths,
            $this->configuration->getFileExtensions(),
            $this->configuration->mustMatchGitDiff()
        );

        // filter out non-PHP php files, e.g. blade templates in Laravel
        $phpFileInfos = array_filter($phpFileInfos, function (SmartFileInfo $smartFileInfo): bool {
            return ! Strings::endsWith($smartFileInfo->getPathname(), '.blade.php');
        });

        return $this->cachedFileInfoFilterAndReporter->filterFileInfos($phpFileInfos);
    }

    private function reportZeroCacheRectorsCondition(): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        if ($this->configuration->shouldClearCache()) {
            return;
        }

        if (! $this->rectorNodeTraverser->hasZeroCacheRectors()) {
            return;
        }

        if ($this->configuration->shouldHideClutter()) {
            return;
        }

        $message = sprintf(
            'Ruleset contains %d rules that need "--clear-cache" option to analyse full project',
            $this->rectorNodeTraverser->getZeroCacheRectorCount()
        );

        $this->symfonyStyle->note($message);
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
}
