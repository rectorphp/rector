<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Application\ErrorCollector;
use Rector\Application\RectorApplication;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\CodingStyle\AfterRectorCodingStyle;
use Rector\Configuration\Configuration;
use Rector\Configuration\Option;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\Console\Shell;
use Rector\FileSystem\FilesFinder;
use Rector\Guard\RectorGuard;
use Rector\Reporting\FileDiff;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ProcessCommand extends Command
{
    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var ProcessCommandReporter
     */
    private $processCommandReporter;

    /**
     * @var AdditionalAutoloader
     */
    private $additionalAutoloader;

    /**
     * @var RectorGuard
     */
    private $rectorGuard;

    /**
     * @var ErrorCollector
     */
    private $errorCollector;

    /**
     * @var AfterRectorCodingStyle
     */
    private $afterRectorCodingStyle;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var RectorApplication
     */
    private $rectorApplication;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        FilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        AdditionalAutoloader $additionalAutoloader,
        RectorGuard $rectorGuard,
        ErrorCollector $errorCollector,
        AfterRectorCodingStyle $afterRectorCodingStyle,
        Configuration $configuration,
        RectorApplication $rectorApplication
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->filesFinder = $phpFilesFinder;
        $this->processCommandReporter = $processCommandReporter;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorGuard = $rectorGuard;
        $this->errorCollector = $errorCollector;
        $this->afterRectorCodingStyle = $afterRectorCodingStyle;
        $this->configuration = $configuration;
        $this->rectorApplication = $rectorApplication;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Reconstruct set of your code.');
        $this->addArgument(
            Option::SOURCE,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Files or directories to be upgraded.'
        );
        $this->addOption(
            Option::OPTION_DRY_RUN,
            'd',
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
            Option::OPTION_WITH_STYLE,
            'w',
            InputOption::VALUE_NONE,
            'Apply basic coding style afterwards to make code look nicer'
        );

        $this->addOption(
            Option::HIDE_AUTOLOAD_ERRORS,
            'e',
            InputOption::VALUE_NONE,
            'Hide autoload errors for the moment.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->rectorGuard->ensureSomeRectorsAreRegistered();

        $source = $input->getArgument(Option::SOURCE);

        // will replace parameter provider in object + method style
        $this->configuration->resolveFromInput($input);

        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($source, ['php']);

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $source);

        $this->rectorApplication->runOnFileInfos($phpFileInfos);

        //        $this->processFileInfos($phpFileInfos);

        $this->processCommandReporter->reportFileDiffs($this->fileDiffs);

        if ($this->errorCollector->getErrors()) {
            $this->processCommandReporter->reportErrors($this->errorCollector->getErrors());
            return Shell::CODE_ERROR;
        }

        if ($this->configuration->isWithStyle()) {
            $this->afterRectorCodingStyle->apply($source);
        }

        $this->symfonyStyle->success('Rector is done!');

        if ($this->configuration->isDryRun() && count($this->fileDiffs)) {
            return Shell::CODE_ERROR;
        }

        return Shell::CODE_SUCCESS;
    }
}
