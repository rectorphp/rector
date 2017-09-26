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

    public function __construct(FileProcessor $fileProcessor, RectorCollector $rectorCollector)
    {
        $this->fileProcessor = $fileProcessor;
        $this->rectorCollector = $rectorCollector;

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

        $source = $input->getArgument(self::ARGUMENT_SOURCE_NAME);
        $files = $this->findPhpFilesInDirectories($source);
        $this->fileProcessor->processFiles($files);

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
}
