<?php declare(strict_types=1);

namespace Rector\Application\FileSystem;

use Rector\Configuration\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Adds and removes scheduled file
 */
final class RemovedAndAddedFilesProcessor
{
    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        Configuration $configuration,
        SymfonyStyle $symfonyStyle,
        Filesystem $filesystem
    ) {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->configuration = $configuration;
        $this->symfonyStyle = $symfonyStyle;
        $this->filesystem = $filesystem;
    }

    public function run(): void
    {
        $this->processAddedFiles();
        $this->processDeletedFiles();
    }

    private function processAddedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getAddedFilesWithContent() as $filePath => $fileContent) {
            if ($this->configuration->isDryRun()) {
                $this->symfonyStyle->note(sprintf('File "%s" will be added:', $filePath));
            } else {
                $this->filesystem->dumpFile($filePath, $fileContent);
                $this->symfonyStyle->note(sprintf('File "%s" was added:', $filePath));
            }

            $this->symfonyStyle->writeln($fileContent);
        }
    }

    private function processDeletedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getRemovedFiles() as $smartFileInfo) {
            if ($this->configuration->isDryRun()) {
                $this->symfonyStyle->warning(sprintf('File "%s" will be removed', $smartFileInfo->getRealPath()));
            } else {
                $this->symfonyStyle->warning(sprintf('File "%s" was removed', $smartFileInfo->getRealPath()));
                $this->filesystem->remove($smartFileInfo->getRealPath());
            }
        }
    }
}
