<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileSystem;

use Rector\Core\Configuration\Configuration;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * Adds and removes scheduled file
 */
final class RemovedAndAddedFilesProcessor
{
    public function __construct(
        private Configuration $configuration,
        private SmartFileSystem $smartFileSystem,
        private NodesWithFileDestinationPrinter $nodesWithFileDestinationPrinter,
        private RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        private SymfonyStyle $symfonyStyle
    ) {
    }

    public function run(): void
    {
        $this->processAddedFilesWithContent();
        $this->processAddedFilesWithNodes();

        $this->processDeletedFiles();
    }

    private function processDeletedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getRemovedFiles() as $removedFile) {
            $relativePath = $removedFile->getRelativeFilePathFromDirectory(getcwd());

            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be removed', $relativePath);
                $this->symfonyStyle->warning($message);
            } else {
                $message = sprintf('File "%s" was removed', $relativePath);
                $this->symfonyStyle->warning($message);
                $this->smartFileSystem->remove($removedFile->getPathname());
            }
        }
    }

    private function processAddedFilesWithContent(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getAddedFilesWithContent() as $addedFileWithContent) {
            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be added', $addedFileWithContent->getFilePath());
                $this->symfonyStyle->note($message);
            } else {
                $this->smartFileSystem->dumpFile(
                    $addedFileWithContent->getFilePath(),
                    $addedFileWithContent->getFileContent()
                );
                $message = sprintf('File "%s" was added', $addedFileWithContent->getFilePath());
                $this->symfonyStyle->note($message);
            }
        }
    }

    private function processAddedFilesWithNodes(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getAddedFilesWithNodes() as $addedFileWithNode) {
            $fileContent = $this->nodesWithFileDestinationPrinter->printNodesWithFileDestination(
                $addedFileWithNode
            );

            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be added', $addedFileWithNode->getFilePath());
                $this->symfonyStyle->note($message);
            } else {
                $this->smartFileSystem->dumpFile($addedFileWithNode->getFilePath(), $fileContent);
                $message = sprintf('File "%s" was added', $addedFileWithNode->getFilePath());
                $this->symfonyStyle->note($message);
            }
        }
    }
}
