<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\FileSystem;
use Rector\Application\FileProcessor;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Exception\Command\FileProcessingException;
use Rector\FileSystem\FilesFinder;
use Rector\Guard\RectorGuard;
use Rector\Reporting\FileDiff;
use Rector\YamlRector\YamlFileProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;

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

    public function __construct(
        FileProcessor $fileProcessor,
        ConsoleStyle $consoleStyle,
        FilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        ParameterProvider $parameterProvider,
        DifferAndFormatter $differAndFormatter,
        AdditionalAutoloader $additionalAutoloader,
        YamlFileProcessor $yamlFileProcessor,
        RectorGuard $rectorGuard
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->additionalAutoloader->autoloadWithInput($input);

        $this->rectorGuard->ensureSomeRectorsAreRegistered();

        $source = $input->getArgument(Option::SOURCE);
        $this->parameterProvider->changeParameter(Option::SOURCE, $source);
        $this->parameterProvider->changeParameter(Option::OPTION_DRY_RUN, $input->getOption(Option::OPTION_DRY_RUN));

        $phpFiles = $this->filesFinder->findInDirectoriesAndFiles($source, ['php']);
        $yamlFiles = $this->filesFinder->findInDirectoriesAndFiles($source, ['yml', 'yaml']);
        $allFiles = $phpFiles + $yamlFiles;

        $this->processCommandReporter->reportLoadedRectors();

        $this->processFiles($allFiles);

        $this->processCommandReporter->reportFileDiffs($this->fileDiffs);
        $this->processCommandReporter->reportChangedFiles($this->changedFiles);

        if ($input->getOption(Option::OPTION_WITH_STYLE)) {
            $command = sprintf('vendor/bin/ecs check %s --config ecs-after-rector.yml --fix', implode(' ', $source));

            $process = new Process($command);
            $process->run();

            $this->consoleStyle->success('Basic coding standard is done');
        }

        $this->consoleStyle->success('Rector is done!');

        return 0;
    }

    /**
     * @param SplFileInfo[] $fileInfos
     */
    private function processFiles(array $fileInfos): void
    {
        $totalFiles = count($fileInfos);
        $this->consoleStyle->title(sprintf('Processing %d file%s', $totalFiles, $totalFiles === 1 ? '' : 's'));
        $this->consoleStyle->progressStart($totalFiles);

        foreach ($fileInfos as $fileInfo) {
            try {
                // php
                if ($fileInfo->getExtension() === 'php') {
                    $this->processFile($fileInfo);
                // yml
                } elseif ($fileInfo->getExtension() === 'yml') {
                    $this->processYamlFile($fileInfo);
                }
            } catch (Throwable $throwable) {
                $this->consoleStyle->newLine();
                throw new FileProcessingException(
                    sprintf('Processing of "%s" file failed.', $fileInfo->getPathname()),
                    $throwable->getCode(),
                    $throwable
                );
            }

            $this->consoleStyle->progressAdvance();
        }

        $this->consoleStyle->newLine(2);
    }

    private function processFile(SplFileInfo $fileInfo): void
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

    private function processYamlFile(SplFileInfo $fileInfo): void
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
}
