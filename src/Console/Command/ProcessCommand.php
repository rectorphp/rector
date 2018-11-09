<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\FileSystem;
use PHPStan\AnalysedCodeException;
use Rector\Application\AppliedRectorCollector;
use Rector\Application\Error;
use Rector\Application\ErrorCollector;
use Rector\Application\FileProcessor;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\CodingStyle\AfterRectorCodingStyle;
use Rector\Configuration\Option;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\Console\Shell;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Contract\Rector\RectorInterface;
use Rector\FileSystem\FilesFinder;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\Guard\RectorGuard;
use Rector\Reporting\FileDiff;
use Rector\YamlRector\YamlFileProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;
use function Safe\sprintf;

final class ProcessCommand extends Command
{
    /**
     * @var string[]
     */
    private $changedFiles = [];

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
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var DifferAndFormatter
     */
    private $differAndFormatter;

    /**
     * @var AdditionalAutoloader
     */
    private $additionalAutoloader;

    /**
     * @var YamlFileProcessor
     */
    private $yamlFileProcessor;

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

    public function __construct(
        FileProcessor $fileProcessor,
        SymfonyStyle $symfonyStyle,
        FilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        ParameterProvider $parameterProvider,
        DifferAndFormatter $differAndFormatter,
        AdditionalAutoloader $additionalAutoloader,
        YamlFileProcessor $yamlFileProcessor,
        RectorGuard $rectorGuard,
        FileSystemFileProcessor $fileSystemFileProcessor,
        ErrorCollector $errorCollector,
        AfterRectorCodingStyle $afterRectorCodingStyle,
        AppliedRectorCollector $appliedRectorCollector
    ) {
        parent::__construct();

        $this->fileProcessor = $fileProcessor;
        $this->symfonyStyle = $symfonyStyle;
        $this->filesFinder = $phpFilesFinder;
        $this->processCommandReporter = $processCommandReporter;
        $this->parameterProvider = $parameterProvider;
        $this->differAndFormatter = $differAndFormatter;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->yamlFileProcessor = $yamlFileProcessor;
        $this->rectorGuard = $rectorGuard;
        $this->fileSystemFileProcessor = $fileSystemFileProcessor;
        $this->errorCollector = $errorCollector;
        $this->afterRectorCodingStyle = $afterRectorCodingStyle;
        $this->appliedRectorCollector = $appliedRectorCollector;
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
        $this->parameterProvider->changeParameter(Option::SOURCE, $source);
        $this->parameterProvider->changeParameter(Option::OPTION_DRY_RUN, $input->getOption(Option::OPTION_DRY_RUN));

        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($source, ['php']);
        $yamlFileInfos = $this->filesFinder->findInDirectoriesAndFiles($source, ['yml', 'yaml']);
        $allFileInfos = $phpFileInfos + $yamlFileInfos;

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $source);

        $this->processFileInfos($allFileInfos, (bool) $input->getOption(Option::HIDE_AUTOLOAD_ERRORS));

        $this->processCommandReporter->reportFileDiffs($this->fileDiffs);
        $this->processCommandReporter->reportChangedFiles($this->changedFiles);

        if ($this->errorCollector->getErrors()) {
            $this->processCommandReporter->reportErrors($this->errorCollector->getErrors());
            return Shell::CODE_ERROR;
        }

        if ($input->getOption(Option::OPTION_WITH_STYLE)) {
            $this->afterRectorCodingStyle->apply($source);
        }

        $this->symfonyStyle->success('Rector is done!');

        return Shell::CODE_SUCCESS;
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    private function processFileInfos(array $fileInfos, bool $shouldHideAutoloadErrors): void
    {
        $totalFiles = count($fileInfos);
        if (! $this->symfonyStyle->isVerbose()) {
            $this->symfonyStyle->progressStart($totalFiles);
        }

        foreach ($fileInfos as $fileInfo) {
            $this->processFileInfo($fileInfo, $shouldHideAutoloadErrors);
            if ($this->symfonyStyle->isVerbose()) {
                $this->symfonyStyle->writeln($fileInfo->getRealPath());
            } else {
                $this->symfonyStyle->progressAdvance();
            }
        }

        $this->symfonyStyle->newLine(2);
    }

    private function processFileInfo(SmartFileInfo $fileInfo, bool $shouldHideAutoloadErrors): void
    {
        try {
            $this->processAnyFile($fileInfo);
        } catch (AnalysedCodeException $analysedCodeException) {
            if ($shouldHideAutoloadErrors) {
                return;
            }

            $message = sprintf(
                'Analyze error: "%s". Try to include your files in "parameters > autoload_paths".%sSee https://github.com/rectorphp/rector#extra-autoloading',
                $analysedCodeException->getMessage(),
                PHP_EOL
            );

            $this->errorCollector->addError(new Error($fileInfo, $message));
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose()) {
                throw $throwable;
            }

            $rectorClass = $this->matchRectorClass($throwable);
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

        if ($this->parameterProvider->provideParameter(Option::OPTION_DRY_RUN)) {
            $newContent = $this->fileProcessor->processFileToString($fileInfo);
            if ($newContent !== $oldContent) {
                $this->fileDiffs[] = new FileDiff(
                    $fileInfo->getPathname(),
                    $this->differAndFormatter->diffAndFormat($oldContent, $newContent),
                    $this->appliedRectorCollector->getRectorClasses()
                );
            }
        } else {
            $newContent = $this->fileProcessor->processFile($fileInfo);
            if ($newContent !== $oldContent) {
                $this->changedFiles[] = $fileInfo->getPathname();
            }
        }
    }

    private function processYamlFile(SmartFileInfo $fileInfo): void
    {
        $oldContent = $fileInfo->getContents();

        if ($this->parameterProvider->provideParameter(Option::OPTION_DRY_RUN)) {
            $newContent = $this->yamlFileProcessor->processFileInfo($fileInfo);
            if ($newContent !== $oldContent) {
                $this->fileDiffs[] = new FileDiff(
                    $fileInfo->getPathname(),
                    $this->differAndFormatter->diffAndFormat($oldContent, $newContent)
                );
            }
        } else {
            $newContent = $this->yamlFileProcessor->processFileInfo($fileInfo);
            if ($newContent !== $oldContent) {
                $this->changedFiles[] = $fileInfo->getPathname();

                FileSystem::write($fileInfo->getPathname(), $newContent);
            }
        }
    }

    private function matchRectorClass(Throwable $throwable): ?string
    {
        if (! isset($throwable->getTrace()[0])) {
            return null;
        }

        /** @var string $class */
        $class = $throwable->getTrace()[0]['class'];
        if (! is_a($class, RectorInterface::class, true)) {
            return null;
        }

        return $class;
    }

    private function processAnyFile(SmartFileInfo $fileInfo): void
    {
        if ($fileInfo->getExtension() === 'php') {
            $this->processFile($fileInfo);
        } elseif (in_array($fileInfo->getExtension(), ['yml', 'yaml'], true)) {
            $this->processYamlFile($fileInfo);
        }

        $this->fileSystemFileProcessor->processFileInfo($fileInfo);
    }
}
