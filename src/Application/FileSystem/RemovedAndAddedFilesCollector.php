<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileSystem;

use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
use Rector\Core\ValueObject\FilePathWithContent;
use Rector\Core\ValueObject\MovedClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemovedAndAddedFilesCollector
{
    /**
     * @var SmartFileInfo[]
     */
    private $removedFiles = [];

    /**
     * @var FilePathWithContent[]
     */
    private $addedFilePathsWithContents = [];

    /**
     * @var MovedClass[]
     */
    private $movedFiles = [];

    /**
     * @var NodesWithFileDestination[]
     */
    private $nodesWithFileDestination = [];

    public function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedFiles[$smartFileInfo->getRealPath()] = $smartFileInfo;
    }

    public function addMovedFile(SmartFileInfo $oldFileInfo, string $newFileLocation, ?string $content = null): void
    {
        // keep original content if none provided
        $content = $content ?: $oldFileInfo->getContents();

        $this->movedFiles[] = new MovedClass($oldFileInfo->getRelativeFilePath(), $newFileLocation, $content);
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getRemovedFiles(): array
    {
        return $this->removedFiles;
    }

    /**
     * @return MovedClass[]
     */
    public function getMovedFiles(): array
    {
        return $this->movedFiles;
    }

    public function isFileRemoved(SmartFileInfo $smartFileInfo): bool
    {
        return isset($this->removedFiles[$smartFileInfo->getRealPath()]);
    }

    public function addFileWithContent(string $filePath, string $content): void
    {
        $this->addedFilePathsWithContents[] = new FilePathWithContent($filePath, $content);
    }

    /**
     * @return FilePathWithContent[]
     */
    public function getAddedFilePathsWithContents(): array
    {
        return $this->addedFilePathsWithContents;
    }

    public function getAffectedFilesCount(): int
    {
        return count($this->addedFilePathsWithContents) + count($this->removedFiles);
    }

    public function getAddedFileCount(): int
    {
        return count($this->addedFilePathsWithContents);
    }

    public function getRemovedFilesCount(): int
    {
        return count($this->removedFiles);
    }

    public function addNodesWithFileDestination(NodesWithFileDestination $nodesWithFileDestination): void
    {
        $this->nodesWithFileDestination[] = $nodesWithFileDestination;
    }

    /**
     * @return NodesWithFileDestination[]
     */
    public function getNodesWithFileDestination(): array
    {
        return $this->nodesWithFileDestination;
    }
}
