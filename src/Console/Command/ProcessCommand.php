<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Application\FileProcessor;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Configuration\Option;
use Rector\Console\ConsoleStyle;
use Rector\Console\Output\ProcessCommandReporter;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Exception\Command\FileProcessingException;
use Rector\Exception\NoRectorsLoadedException;
use Rector\FileSystem\PhpFilesFinder;
use Rector\Naming\CommandNaming;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\Reporting\FileDiff;
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
     * @var FileProcessor
     */
    private $fileProcessor;

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
     * @var FileDiff[]
     */
    private $fileDiffs = [];

    /**
     * @var AdditionalAutoloader
     */
    private $additionalAutoloader;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    public function __construct(
        FileProcessor $fileProcessor,
        ConsoleStyle $consoleStyle,
        PhpFilesFinder $phpFilesFinder,
        ProcessCommandReporter $processCommandReporter,
        ParameterProvider $parameterProvider,
        DifferAndFormatter $differAndFormatter,
        AdditionalAutoloader $additionalAutoloader,
        RectorNodeTraverser $rectorNodeTraverser
    ) {
        parent::__construct();

        $this->fileProcessor = $fileProcessor;
        $this->consoleStyle = $consoleStyle;
        $this->phpFilesFinder = $phpFilesFinder;
        $this->processCommandReporter = $processCommandReporter;
        $this->parameterProvider = $parameterProvider;
        $this->differAndFormatter = $differAndFormatter;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->additionalAutoloader->autoloadWithInput($input);

        $this->ensureSomeRectorsAreRegistered();

        $source = $input->getArgument(Option::SOURCE);
        $this->parameterProvider->changeParameter(Option::SOURCE, $source);
        $this->parameterProvider->changeParameter(Option::OPTION_DRY_RUN, $input->getOption(Option::OPTION_DRY_RUN));
        $files = $this->phpFilesFinder->findInDirectoriesAndFiles($source);

        $this->processCommandReporter->reportLoadedRectors();

        $this->processFiles($files);

        $this->processCommandReporter->reportFileDiffs($this->fileDiffs);
        $this->processCommandReporter->reportChangedFiles($this->changedFiles);
        $this->consoleStyle->success('Rector is done!');

        return 0;
    }

    private function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->rectorNodeTraverser->getRectorCount() > 0) {
            return;
        }

        throw new NoRectorsLoadedException(
            'No rectors were found. Registers them in rector.yml config to "rector:" '
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

        foreach ($fileInfos as $fileInfo) {
            try {
                $this->processFile($fileInfo);
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
}
