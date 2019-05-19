<?php declare(strict_types=1);

namespace Rector\Application\FileSystem;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

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

    public function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedFiles[$smartFileInfo->getRealPath()] = $smartFileInfo;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getRemovedFiles(): array
    {
        return $this->removedFiles;
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
