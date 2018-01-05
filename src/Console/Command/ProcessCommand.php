<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Application\FileProcessor;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Exception\Command\FileProcessingException;
use Rector\Exception\NoRectorsLoadedException;
use Rector\FileSystem\PhpFilesFinder;
use Rector\Naming\CommandNaming;
use Rector\Rector\RectorCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;

final class ProcessCommand extends Command
{
    /**
     * @var string
     */
    private const ARGUMENT_SOURCE_NAME = 'source';

    /**
     * @var string
     */
    private const OPTION_DRY_RUN = 'dry-run';

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var RectorCollector
     */
    private $rectorCollector;

    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var PhpFilesFinder
     */
    private $phpFilesFinder;

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
     * @var string[][]
     */
    private $diffFiles = [];

    public function __construct(
        FileProcessor $fileProcessor,
        RectorCollector $rectorCollector,
        ConsoleStyle $consoleStyle,
        PhpFilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        ParameterProvider $parameterProvider,
        DifferAndFormatter $differAndFormatter
    ) {
        parent::__construct();

        $this->fileProcessor = $fileProcessor;
        $this->rectorCollector = $rectorCollector;
        $this->consoleStyle = $consoleStyle;
        $this->phpFilesFinder = $phpFilesFinder;
        $this->processCommandReporter = $processCommandReporter;
        $this->parameterProvider = $parameterProvider;
        $this->differAndFormatter = $differAndFormatter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Reconstruct set of your code.');
        $this->addArgument(
            self::ARGUMENT_SOURCE_NAME,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'Files or directories to be upgraded.'
        );
        $this->addOption(
            self::OPTION_DRY_RUN,
            null,
            InputOption::VALUE_NONE,
            'See diff of changes, do not save them to files.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->consoleStyle->setVerbosity($output->getVerbosity());

        $this->ensureSomeRectorsAreRegistered();

        $source = $input->getArgument(self::ARGUMENT_SOURCE_NAME);
        $this->parameterProvider->changeParameter(self::ARGUMENT_SOURCE_NAME, $source);
        $this->parameterProvider->changeParameter(self::OPTION_DRY_RUN, $input->getOption(self::OPTION_DRY_RUN));
        $files = $this->phpFilesFinder->findInDirectoriesAndFiles($source);

        $this->processCommandReporter->reportLoadedRectors();

        // for testing purposes
        array_splice($files, 0, -100);

        $this->processFiles($files);

        if (count($this->diffFiles) > 0) {
            $this->processCommandReporter->reportDiffFiles($this->diffFiles);
        }

        if (count($this->changedFiles) > 0) {
            $this->processCommandReporter->reportChangedFiles($this->changedFiles);
        }

        $this->consoleStyle->success('Rector is done!');

        return 0;
    }

    private function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->rectorCollector->getRectorCount() > 0) {
            return;
        }

        throw new NoRectorsLoadedException(
            'No rector were found. Registers them in rector.yml config to "rector:" '
            . 'section, load them via "--config <file>.yml" or "--level <level>" CLI options.'
        );
    }

    /**
     * @param SplFileInfo[] $fileInfos
     */
    private function processFiles(array $fileInfos): void
    {
        $totalFiles = count($fileInfos);
        $this->consoleStyle->title(sprintf('Processing %d file%s', $totalFiles, $totalFiles === 1 ? '' : 's'));
        $this->consoleStyle->progressStart($totalFiles);

        $i = 0;
        foreach ($fileInfos as $fileInfo) {
            try {
                $this->processFile($fileInfo, $i);
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

    private function processFile(SplFileInfo $fileInfo, int &$i): void
    {
        $oldContent = $fileInfo->getContents();

        if ($this->parameterProvider->provideParameter(self::OPTION_DRY_RUN)) {
            $newContent = $this->fileProcessor->processFileToString($fileInfo);
            if ($newContent !== $oldContent) {
                $this->diffFiles[] = [
                    'file' => sprintf('<options=bold>%d) %s</>', ++$i, $fileInfo->getPathname()),
                    'diff' => $this->differAndFormatter->diffAndFormat($oldContent, $newContent),
                ];
            }
        } else {
            $newContent = $this->fileProcessor->processFile($fileInfo);
            if ($newContent !== $oldContent) {
                $this->changedFiles[] = $fileInfo->getPathname();
            }
        }
    }
}
