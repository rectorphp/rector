<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Console\Command;

use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

final class ExtractCommand extends Command
{
    /**
     * @var string
     */
    private const NAME = 'extract-deprecations';

    /**
     * @var string
     */
    private const ARGUMENT_SOURCE_NAME = 'source';

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription('Extract deprecation notes from PHP files in directory(ies).');
        $this->addArgument(
            self::ARGUMENT_SOURCE_NAME,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'One or more directory to be checked.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = $input->getArgument(self::ARGUMENT_SOURCE_NAME);
        $files = $this->findPhpFilesInDirectories($source);

        dump($files);
        die;

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
