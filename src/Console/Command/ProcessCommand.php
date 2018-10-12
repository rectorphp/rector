<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\FileSystem;
use PHPStan\AnalysedCodeException;
use Rector\Application\Error;
use Rector\Application\FileProcessor;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\Console\Shell;
use Rector\ConsoleDiffer\DifferAndFormatter;
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
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;
use function Safe\sprintf;

final class ProcessCommand extends Command
{
    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

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
     * @var string[]
     */
    private $changedFiles = [];

    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];

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
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    public function __construct(
        FileProcessor $fileProcessor,
        ConsoleStyle $consoleStyle,
        FilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        ParameterProvider $parameterProvider,
        DifferAndFormatter $differAndFormatter,
        AdditionalAutoloader $additionalAutoloader,
        YamlFileProcessor $yamlFileProcessor,
        RectorGuard $rectorGuard,
        FileSystemFileProcessor $fileSystemFileProcessor
    ) {
        parent::__construct();

        $this->fileProcessor = $fileProcessor;
        $this->consoleStyle = $consoleStyle;
        $this->filesFinder = $phpFilesFinder;
        $this->processCommandReporter = $processCommandReporter;
        $this->parameterProvider = $parameterProvider;
        $this->differAndFormatter = $differAndFormatter;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->yamlFileProcessor = $yamlFileProcessor;
        $this->rectorGuard = $rectorGuard;
        $this->fileSystemFileProcessor = $fileSystemFileProcessor;
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
            null,
            InputOption::VALUE_NONE,
            'See diff of changes, do not save them to files.'
        );
        $this->addOption(
            Option::OPTION_AUTOLOAD_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'File with extra autoload'
        );

        $this->addOption(
            Option::OPTION_WITH_STYLE,
            null,
            InputOption::VALUE_NONE,
            'Apply basic coding style afterwards to make code look nicer'
        );

        $this->addOption(
            Option::HIDE_AUTOLOAD_ERRORS,
            null,
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

        $this->processCommandReporter->reportLoadedRectors();

        $this->processFileInfos($allFileInfos, (bool) $input->getOption(Option::HIDE_AUTOLOAD_ERRORS));

        $this->processCommandReporter->reportFileDiffs($this->fileDiffs);
        $this->processCommandReporter->reportChangedFiles($this->changedFiles);

        if ($this->errors) {
            $this->processCommandReporter->reportErrors($this->errors);
            return Shell::CODE_ERROR;
        }

        if ($input->getOption(Option::OPTION_WITH_STYLE)) {
            $command = sprintf(
                'vendor/bin/ecs check %s --config %s --fix',
                implode(' ', $source),
                __DIR__ . '/../../../ecs-after-rector.yml'
            );

            $process = new Process($command);
            $process->run();

            if ($process->isSuccessful()) {
                $this->consoleStyle->success('Basic coding standard is done');
            } else {
                $this->consoleStyle->error(
                    sprintf('Basic coding standard was not applied due to: "%s"', $process->getErrorOutput())
                );
            }
        }

        $this->consoleStyle->success('Rector is done!');

        return Shell::CODE_SUCCESS;
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    private function processFileInfos(array $fileInfos, bool $shouldHideAutoloadErrors): void
    {
        $totalFiles = count($fileInfos);
        $this->consoleStyle->title(sprintf('Processing %d file%s', $totalFiles, $totalFiles === 1 ? '' : 's'));
        $this->consoleStyle->progressStart($totalFiles);

        foreach ($fileInfos as $fileInfo) {
            $this->processFileInfo($fileInfo, $shouldHideAutoloadErrors);
            $this->consoleStyle->progressAdvance();
        }

        $this->consoleStyle->newLine(2);
    }

    private function processFileInfo(SmartFileInfo $fileInfo, bool $shouldHideAutoloadErrors): void
    {
        try {
            if ($fileInfo->getExtension() === 'php') {
                $this->processFile($fileInfo);
            } elseif (in_array($fileInfo->getExtension(), ['yml', 'yaml'], true)) {
                $this->processYamlFile($fileInfo);
            }

            $this->processFileSystemFile($fileInfo);
        } catch (AnalysedCodeException $analysedCodeException) {
            if ($shouldHideAutoloadErrors) {
                return;
            }

            $message = sprintf(
                'Analyze error: "%s". Try to include your files in "parameters > autoload_paths".%sSee https://github.com/rectorphp/rector#extra-autoloading',
                $analysedCodeException->getMessage(),
                PHP_EOL
            );

            $this->errors[] = new Error($fileInfo, $message, null);
        } catch (Throwable $throwable) {
            $this->errors[] = new Error($fileInfo, $throwable->getMessage(), $throwable->getCode());
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
                    $this->differAndFormatter->diffAndFormat($oldContent, $newContent)
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

    private function processFileSystemFile(SmartFileInfo $fileInfo): void
    {
        $this->fileSystemFileProcessor->processFileInfo($fileInfo);
    }
}
