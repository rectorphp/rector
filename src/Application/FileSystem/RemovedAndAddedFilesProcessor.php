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
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        Configuration $configuration,
        SymfonyStyle $symfonyStyle,
        Filesystem $filesystem,
    NodesWithFileDestinationPrinter $nodesWithFileDestinationPrinter
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
                $this->symfonyStyle->note(sprintf('File "%s" will be added:', $filePath));
            } else {
                $this->filesystem->dumpFile($filePath, $fileContent);
                $this->symfonyStyle->note(sprintf('File "%s" was added:', $filePath));
            }

            $this->symfonyStyle->writeln($fileContent);
        }

        foreach ($this->removedAndAddedFilesCollector->getNodesWithFileDestination() as $nodesWithFileDestination) {
            $fileContent = $this->nodesWithFileDestinationPrinter->printNodesWithFileDestination(
                $nodesWithFileDestination
            );

            if ($this->configuration->isDryRun()) {
                $this->symfonyStyle->note(
                    sprintf('File "%s" will be added:', $nodesWithFileDestination->getFileDestination())
                );
            } else {
                $this->filesystem->dumpFile($nodesWithFileDestination->getFileDestination(), $fileContent);
                $this->symfonyStyle->note(
                    sprintf('File "%s" was added:', $nodesWithFileDestination->getFileDestination())
                );
            }

            $this->symfonyStyle->writeln($fileContent);
        }
    }

    private function processDeletedFiles(): void
    {
        foreach ($this->removedAndAddedFilesCollector->getRemovedFiles() as $smartFileInfo) {
            $relativePath = $smartFileInfo->getRelativeFilePathFromDirectory(getcwd());

            if ($this->configuration->isDryRun()) {
                $this->symfonyStyle->warning(sprintf('File "%s" will be removed', $relativePath));
            } else {
                $this->symfonyStyle->warning(sprintf('File "%s" was removed', $relativePath));
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
        $this->symfonyStyle->warning(sprintf(
            'File "%s" %s moved to "%s"',
            $movedClassValueObject->getOldPath(),
            $verb,
            $movedClassValueObject->getNewPath()
        ));
    }
}
