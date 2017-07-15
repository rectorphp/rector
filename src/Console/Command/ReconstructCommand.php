<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\Finder;
use Rector\Application\FileProcessor;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReconstructCommand extends Command
{
    /**
     * @var string
     */
    private const NAME = 'reconstruct';

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    public function __construct(FileProcessor $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;

        parent::__construct();
    }

    /**
     * @var string
     */
    private const ARGUMENT_SOURCE_NAME = 'source';

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription('Reconstruct set of your code.');

        // @todo: use modular configure from ApiGen
        $this->addArgument(
            self::ARGUMENT_SOURCE_NAME,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'The path(s) to be checked.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
            ->in($directories);

        return iterator_to_array($finder->getIterator());
    }
}