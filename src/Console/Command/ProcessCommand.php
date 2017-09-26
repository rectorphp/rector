<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\Finder;
use Rector\Application\FileProcessor;
use Rector\Exception\NoRectorsLoadedException;
use Rector\Naming\CommandNaming;
use Rector\Rector\RectorCollector;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProcessCommand extends Command
{
    /**
     * @var string
     */
    private const ARGUMENT_SOURCE_NAME = 'source';

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var RectorCollector
     */
    private $rectorCollector;
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        FileProcessor $fileProcessor,
        RectorCollector $rectorCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->fileProcessor = $fileProcessor;
        $this->rectorCollector = $rectorCollector;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Reconstruct set of your code.');
        $this->addArgument(
            self::ARGUMENT_SOURCE_NAME,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'The path(s) to be checked.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureSomeRectorsAreRegistered();

        $this->reportLoadedRectors();

        $source = $input->getArgument(self::ARGUMENT_SOURCE_NAME);
        $files = $this->findPhpFilesInDirectories($source);

        $this->reportFiles($files);

        // find files fix :)
//        dump($files);
//        die;

        $this->fileProcessor->processFiles($files);

        # report: changed x files

        return 0;
    }

    /**
     * @param string[] $directories
     * @return SplFileInfo[] array
     */
    private function findPhpFilesInDirectories(array $directories): array
    {
        $finder = Finder::find('*.php')
            ->from($directories);

        return iterator_to_array($finder->getIterator());
    }

    private function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->rectorCollector->getRectorCount() > 0) {
            return;
        }

        throw new NoRectorsLoadedException(
            'No rector were found. Registers them in rector.yml config to "rector:" '
            . 'section or load them via "--config <file>.yml" CLI option.'
        );
    }

    private function reportLoadedRectors(): void
    {
        $this->symfonyStyle->title(sprintf(
            '%d Loaded Rector%s',
            $this->rectorCollector->getRectorCount(),
            $this->rectorCollector->getRectorCount() === 1 ? '' : 's'
        ));

        foreach ($this->rectorCollector->getRectors() as $rector) {
            $this->symfonyStyle->writeln(sprintf(
                ' - %s',
                get_class($rector)
            ));
        }

        $this->symfonyStyle->newLine();
    }

    /**
     * @var string[]
     */
    private function reportFiles(array $files): void
    {
        $this->symfonyStyle->title('Processing files');

        foreach ($files as $file) {
            $this->symfonyStyle->writeln(sprintf(
                ' - %s',
                $file
            ));
        }

        $this->symfonyStyle->newLine();
    }
}
