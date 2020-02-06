<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileSystem;

use Rector\Core\ValueObject\MovedClassValueObject;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemovedAndAddedFilesCollector
{
    /**
     * @var SmartFileInfo[]
     */
    private $removedFiles = [];

    /**
     * @var string[]
     */
    private $addedFilesWithContent = [];

    /**
     * @var MovedClassValueObject[]
     */
    private $movedFiles = [];

    public function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedFiles[$smartFileInfo->getRealPath()] = $smartFileInfo;
    }

    public function addMovedFile(SmartFileInfo $oldFileInfo, string $newFileLocation, ?string $content = null): void
    {
        // keep original content if none provided
        $content = $content ?: $oldFileInfo->getContents();

        $this->movedFiles[] = new MovedClassValueObject(
            $oldFileInfo->getRelativeFilePath(),
            $newFileLocation,
            $content
        );
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getRemovedFiles(): array
    {
        return $this->removedFiles;
    }

    /**
     * @return MovedClassValueObject[]
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
        $this->addedFilesWithContent[$filePath] = $content;
    }

    /**
     * @return string[]
     */
    public function getAddedFilesWithContent(): array
    {
        return $this->addedFilesWithContent;
    }

    public function getAffectedFilesCount(): int
    {
        return count($this->addedFilesWithContent) + count($this->removedFiles);
    }
}
