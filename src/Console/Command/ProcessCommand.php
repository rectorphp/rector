<?php declare(strict_types=1);

namespace Rector\Console\Command;

use PHPStan\AnalysedCodeException;
use Rector\Application\AppliedRectorCollector;
use Rector\Application\Error;
use Rector\Application\ErrorCollector;
use Rector\Application\FileProcessor;
use Rector\Application\FilesToReprintCollector;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\CodingStyle\AfterRectorCodingStyle;
use Rector\Configuration\Configuration;
use Rector\Configuration\Option;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\Console\Shell;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Error\ExceptionCorrector;
use Rector\FileSystem\FilesFinder;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\Guard\RectorGuard;
use Rector\Reporting\FileDiff;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Throwable;

final class ProcessCommand extends Command
{
    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

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
     * @var DifferAndFormatter
     */
    private $differAndFormatter;

    /**
     * @var AdditionalAutoloader
     */
    private $additionalAutoloader;

    /**
     * @var RectorGuard
     */
    private $rectorGuard;

    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    /**
     * @var ErrorCollector
     */
    private $errorCollector;

    /**
     * @var AfterRectorCodingStyle
     */
    private $afterRectorCodingStyle;

    /**
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

    /**
     * @var FilesToReprintCollector
     */
    private $filesToReprintCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ExceptionCorrector
     */
    private $exceptionCorrector;

    public function __construct(
        FileProcessor $fileProcessor,
        SymfonyStyle $symfonyStyle,
        FilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        DifferAndFormatter $differAndFormatter,
        AdditionalAutoloader $additionalAutoloader,
        RectorGuard $rectorGuard,
        FileSystemFileProcessor $fileSystemFileProcessor,
        ErrorCollector $errorCollector,
        AfterRectorCodingStyle $afterRectorCodingStyle,
        AppliedRectorCollector $appliedRectorCollector,
        FilesToReprintCollector $filesToReprintCollector,
        Configuration $configuration,
        ExceptionCorrector $exceptionCorrector
    ) {
        parent::__construct();

        $this->fileProcessor = $fileProcessor;
        $this->symfonyStyle = $symfonyStyle;
        $this->filesFinder = $phpFilesFinder;
        $this->processCommandReporter = $processCommandReporter;
        $this->differAndFormatter = $differAndFormatter;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorGuard = $rectorGuard;
        $this->fileSystemFileProcessor = $fileSystemFileProcessor;
        $this->errorCollector = $errorCollector;
        $this->afterRectorCodingStyle = $afterRectorCodingStyle;
        $this->appliedRectorCollector = $appliedRectorCollector;
        $this->filesToReprintCollector = $filesToReprintCollector;
        $this->configuration = $configuration;
        $this->exceptionCorrector = $exceptionCorrector;
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

        $this->processFileInfos($phpFileInfos);

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

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    private function processFileInfos(array $fileInfos): void
    {
        $totalFiles = count($fileInfos);
        if (! $this->symfonyStyle->isVerbose()) {
            $this->symfonyStyle->progressStart($totalFiles);
        }

        foreach ($fileInfos as $fileInfo) {
            $this->processFileInfo($fileInfo);
            if ($this->symfonyStyle->isVerbose()) {
                $this->symfonyStyle->writeln($fileInfo->getRealPath());
            } else {
                $this->symfonyStyle->progressAdvance();
            }
        }

        $this->symfonyStyle->newLine(2);
    }

    private function processFileInfo(SmartFileInfo $fileInfo): void
    {
        try {
            $this->processFile($fileInfo);
            $this->fileSystemFileProcessor->processFileInfo($fileInfo);
        } catch (AnalysedCodeException $analysedCodeException) {
            if ($this->configuration->shouldHideAutoloadErrors()) {
                return;
            }

            $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);

            $this->errorCollector->addError(new Error($fileInfo, $message));
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $rectorClass = $this->exceptionCorrector->matchRectorClass($throwable);
            if ($rectorClass) {
                $this->errorCollector->addErrorWithRectorMessage($rectorClass, $throwable->getMessage());
            } else {
                $this->errorCollector->addError(new Error($fileInfo, $throwable->getMessage(), $throwable->getCode()));
            }
        }
    }

    private function processFile(SmartFileInfo $fileInfo): void
    {
        $oldContent = $fileInfo->getContents();

        if ($this->configuration->isDryRun()) {
            $newContent = $this->fileProcessor->processFileToString($fileInfo);

            foreach ($this->filesToReprintCollector->getFileInfos() as $fileInfoToReprint) {
                $reprintedOldContent = $fileInfoToReprint->getContents();
                $reprintedNewContent = $this->fileProcessor->reprintToString($fileInfoToReprint);
                $this->recordFileDiff($fileInfoToReprint, $reprintedNewContent, $reprintedOldContent);
            }
        } else {
            $newContent = $this->fileProcessor->processFile($fileInfo);

            foreach ($this->filesToReprintCollector->getFileInfos() as $fileInfoToReprint) {
                $reprintedOldContent = $fileInfoToReprint->getContents();
                $reprintedNewContent = $this->fileProcessor->reprintFile($fileInfoToReprint);
                $this->recordFileDiff($fileInfoToReprint, $reprintedNewContent, $reprintedOldContent);
            }
        }

        $this->recordFileDiff($fileInfo, $newContent, $oldContent);
        $this->filesToReprintCollector->reset();
    }

    private function recordFileDiff(SmartFileInfo $fileInfo, string $newContent, string $oldContent): void
    {
        if ($newContent === $oldContent) {
            return;
        }

        // always keep the most recent diff
        $this->fileDiffs[$fileInfo->getRealPath()] = new FileDiff(
            $fileInfo->getRealPath(),
            $this->differAndFormatter->diffAndFormat($oldContent, $newContent),
            $this->appliedRectorCollector->getRectorClasses()
        );
    }
}
