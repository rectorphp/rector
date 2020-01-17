<?php

declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Application\ErrorAndDiffCollector;
use Rector\Application\RectorApplication;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Configuration\Configuration;
use Rector\Configuration\Option;
use Rector\Console\Output\ConsoleOutputFormatter;
use Rector\Console\Output\OutputFormatterCollector;
use Rector\Console\Shell;
use Rector\Extension\ReportingExtensionRunner;
use Rector\FileSystem\FilesFinder;
use Rector\Guard\RectorGuard;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Stubs\StubLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

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
     * @var string[]
     */
    private $fileExtensions = [];

    /**
     * @var OutputFormatterCollector
     */
    private $outputFormatterCollector;

    /**
     * @var ReportingExtensionRunner
     */
    private $reportingExtensionRunner;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var StubLoader
     */
    private $stubLoader;

    /**
     * @var string[]
     */
    private $paths = [];

    /**
     * @param string[] $paths
     * @param string[] $fileExtensions
     */
    public function __construct(
        FilesFinder $phpFilesFinder,
        AdditionalAutoloader $additionalAutoloader,
        RectorGuard $rectorGuard,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        RectorApplication $rectorApplication,
        OutputFormatterCollector $outputFormatterCollector,
        ReportingExtensionRunner $reportingExtensionRunner,
        RectorNodeTraverser $rectorNodeTraverser,
        StubLoader $stubLoader,
        array $paths,
        array $fileExtensions
    ) {
        $this->filesFinder = $phpFilesFinder;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorGuard = $rectorGuard;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->rectorApplication = $rectorApplication;
        $this->fileExtensions = $fileExtensions;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->reportingExtensionRunner = $reportingExtensionRunner;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->stubLoader = $stubLoader;

        parent::__construct();
        $this->paths = $paths;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
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
            Option::HIDE_AUTOLOAD_ERRORS,
            'e',
            InputOption::VALUE_NONE,
            'Hide autoload errors for the moment.'
        );

        $this->addOption(
            Option::MATCH_GIT_DIFF,
            null,
            InputOption::VALUE_NONE,
            'Execute only on file(s) matching the git diff.'
        );

        $this->addOption(
            Option::OPTION_ONLY,
            'r',
            InputOption::VALUE_REQUIRED,
            'Run only one single Rector from the loaded Rectors (in services, sets, etc).'
        );

        $availableOutputFormatters = $this->outputFormatterCollector->getNames();
        $this->addOption(
            Option::OPTION_OUTPUT_FORMAT,
            'o',
            InputOption::VALUE_OPTIONAL,
            sprintf('Select output format: "%s".', implode('", "', $availableOutputFormatters)),
            ConsoleOutputFormatter::NAME
        );

        $this->addOption(
            Option::OPTION_NO_PROGRESS_BAR,
            null,
            InputOption::VALUE_NONE,
            'Hide progress bar. Useful e.g. for nicer CI output.'
        );

        $this->addOption(
            Option::OPTION_OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Location for file to dump result in. Useful for Docker or automated processes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configuration->resolveFromInput($input);
        $this->configuration->setAreAnyPhpRectorsLoaded((bool) $this->rectorNodeTraverser->getPhpRectorCount());

        $this->rectorGuard->ensureSomeRectorsAreRegistered();
        $this->stubLoader->loadStubs();

        $source = $this->resolvesSourcePaths($input);

        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $source,
            $this->fileExtensions,
            $this->configuration->mustMatchGitDiff()
        );

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $source);

        $this->rectorApplication->runOnFileInfos($phpFileInfos);

        // report diffs and errors
        $outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);
        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($this->errorAndDiffCollector);

        $this->reportingExtensionRunner->run();

        // some errors were found → fail
        if ($this->errorAndDiffCollector->getErrors() !== []) {
            return Shell::CODE_ERROR;
        }

        // inverse error code for CI dry-run
        if ($this->configuration->isDryRun() && $this->errorAndDiffCollector->getFileDiffsCount()) {
            return Shell::CODE_ERROR;
        }

        return Shell::CODE_SUCCESS;
    }

    /**
     * @return string[]
     */
    private function resolvesSourcePaths(InputInterface $input): array
    {
        $commandLinePaths = (array) $input->getArgument(Option::SOURCE);

        // manual command line value has priority
        if (count($commandLinePaths) > 0) {
            return $commandLinePaths;
        }

        // fallback to config defined paths
        return $this->paths;
    }
}
