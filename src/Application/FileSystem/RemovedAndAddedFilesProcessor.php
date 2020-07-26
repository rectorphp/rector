<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileSystem;

use Rector\Core\Configuration\Configuration;
use Rector\Core\PhpParser\Printer\NodesWithFileDestinationPrinter;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\Core\ValueObject\MovedClassValueObject;
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

    /**
     * @var NodesWithFileDestinationPrinter
     */
    private $nodesWithFileDestinationPrinter;

    public function __construct(
        Configuration $configuration,
        Filesystem $filesystem,
        NodesWithFileDestinationPrinter $nodesWithFileDestinationPrinter,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->configuration = $configuration;
        $this->symfonyStyle = $symfonyStyle;
        $this->filesystem = $filesystem;
        $this->nodesWithFileDestinationPrinter = $nodesWithFileDestinationPrinter;
    }

    public function run(): void
    {
        $this->processAddedFiles();
        $this->processDeletedFiles();
        $this->processMovedFiles();
    }

    private function processAddedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getAddedFilesWithContent() as $filePath => $fileContent) {
            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be added:', $filePath);
                $this->symfonyStyle->note($message);
            } else {
                $this->filesystem->dumpFile($filePath, $fileContent);
                $message = sprintf('File "%s" was added:', $filePath);
                $this->symfonyStyle->note($message);
            }

            $this->symfonyStyle->writeln($fileContent);
        }

        foreach ($this->removedAndAddedFilesCollector->getNodesWithFileDestination() as $nodesWithFileDestination) {
            $fileContent = $this->nodesWithFileDestinationPrinter->printNodesWithFileDestination(
                $nodesWithFileDestination
            );

            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be added:', $nodesWithFileDestination->getFileDestination());
                $this->symfonyStyle->note($message);
            } else {
                $this->filesystem->dumpFile($nodesWithFileDestination->getFileDestination(), $fileContent);
                $message = sprintf('File "%s" was added:', $nodesWithFileDestination->getFileDestination());
                $this->symfonyStyle->note($message);
            }

            $this->symfonyStyle->writeln('----------------------------------------');
            $this->symfonyStyle->writeln($fileContent);
            $this->symfonyStyle->writeln('----------------------------------------');
        }
    }

    private function processDeletedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getRemovedFiles() as $smartFileInfo) {
            $relativePath = $smartFileInfo->getRelativeFilePathFromDirectory(getcwd());

            if ($this->configuration->isDryRun()) {
                $message = sprintf('File "%s" will be removed', $relativePath);
                $this->symfonyStyle->warning($message);
            } else {
                $message = sprintf('File "%s" was removed', $relativePath);
                $this->symfonyStyle->warning($message);
                $this->filesystem->remove($smartFileInfo->getRealPath());
            }
        }
    }

    private function processMovedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getMovedFiles() as $movedClassValueObject) {
            if ($this->configuration->isDryRun() && ! StaticPHPUnitEnvironment::isPHPUnitRun()) {
                $this->printFileMoveWarning($movedClassValueObject, 'will be');
            } else {
                $this->printFileMoveWarning($movedClassValueObject, 'was');

                $this->filesystem->remove($movedClassValueObject->getOldPath());

                $this->filesystem->dumpFile(
                    $movedClassValueObject->getNewPath(),
                    $movedClassValueObject->getFileContent()
                );
            }
        }
    }

    private function printFileMoveWarning(MovedClassValueObject $movedClassValueObject, string $verb): void
    {
        $message = sprintf(
            'File "%s" %s moved to "%s"',
            $movedClassValueObject->getOldPath(),
            $verb,
            $movedClassValueObject->getNewPath()
        );
        $this->symfonyStyle->warning($message);
    }
}
