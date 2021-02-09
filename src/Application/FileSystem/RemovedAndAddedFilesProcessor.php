<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileSystem;

use Rector\Core\Configuration\Configuration;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\FileSystemRector\Contract\MovedFileInterface;
use Rector\FileSystemRector\ValueObject\MovedFileWithContent;
use Rector\FileSystemRector\ValueObject\MovedFileWithNodes;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileSystem;

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
     * @var NodesWithFileDestinationPrinter
     */
    private $nodesWithFileDestinationPrinter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        NodesWithFileDestinationPrinter $nodesWithFileDestinationPrinter,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        SymfonyStyle $symfonyStyle,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->configuration = $configuration;
        $this->symfonyStyle = $symfonyStyle;
        $this->nodesWithFileDestinationPrinter = $nodesWithFileDestinationPrinter;
        $this->smartFileSystem = $smartFileSystem;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function run(): void
    {
        $this->processAddedFilesWithContent();
        $this->processAddedFilesWithNodes();

        $this->processMovedFiles();
        $this->processMovedFilesWithNodes();

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

    private function processMovedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getMovedFiles() as $movedFile) {
            if ($this->configuration->isDryRun() && ! StaticPHPUnitEnvironment::isPHPUnitRun()) {
                $this->printFileMoveWarning($movedFile, 'will be');
            } else {
                $this->printFileMoveWarning($movedFile, 'was');

                $this->smartFileSystem->remove($movedFile->getOldPathname());

                $fileContent = $this->resolveFileContentFromMovedFile($movedFile);
                $this->smartFileSystem->dumpFile($movedFile->getNewPathname(), $fileContent);
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
        foreach ($this->removedAndAddedFilesCollector->getAddedFilesWithNodes() as $addedFilesWithNode) {
            $fileContent = $this->nodesWithFileDestinationPrinter->printNodesWithFileDestination(
                $addedFilesWithNode
            );

            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be added', $addedFilesWithNode->getFilePath());
                $this->symfonyStyle->note($message);
            } else {
                $this->smartFileSystem->dumpFile($addedFilesWithNode->getFilePath(), $fileContent);
                $message = sprintf('File "%s" was added', $addedFilesWithNode->getFilePath());
                $this->symfonyStyle->note($message);
            }
        }
    }

    private function processMovedFilesWithNodes(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getMovedFileWithNodes() as $movedFileWithNodes) {
            $fileContent = $this->nodesWithFileDestinationPrinter->printNodesWithFileDestination(
                $movedFileWithNodes
            );

            if ($this->configuration->isDryRun()) {
                $message = sprintf(
                    'File "%s" will be moved to "%s"',
                    $movedFileWithNodes->getOldPathname(),
                    $movedFileWithNodes->getNewPathname()
                );
                $this->symfonyStyle->note($message);
            } else {
                $this->smartFileSystem->dumpFile($movedFileWithNodes->getNewPathname(), $fileContent);
                $message = sprintf(
                    'File "%s" was moved to "%s":',
                    $movedFileWithNodes->getOldPathname(),
                    $movedFileWithNodes->getNewPathname(),
                );
                $this->symfonyStyle->note($message);
            }
        }
    }

    private function printFileMoveWarning(MovedFileInterface $movedFile, string $verb): void
    {
        $message = sprintf(
            'File "%s" %s moved to "%s"',
            $movedFile->getOldPathname(),
            $verb,
            $movedFile->getNewPathname()
        );

        $this->symfonyStyle->warning($message);
    }

    private function resolveFileContentFromMovedFile(MovedFileInterface $movedFile): string
    {
        if ($movedFile instanceof MovedFileWithContent) {
            return $movedFile->getFileContent();
        }

        if ($movedFile instanceof MovedFileWithNodes) {
            return $this->betterStandardPrinter->prettyPrintFile($movedFile->getNodes());
        }

        throw new NotImplementedYetException(get_class($movedFile));
    }
}
