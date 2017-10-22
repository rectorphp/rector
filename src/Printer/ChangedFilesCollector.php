<?php declare(strict_types=1);

namespace Rector\Printer;

use SplFileInfo;

final class ChangedFilesCollector
{
    /**
     * @var SplFileInfo[]
     */
    private $changedFiles = [];

    public function addChangedFile(SplFileInfo $fileInfo): void
    {
        $this->changedFiles[] = $fileInfo;
    }

    public function getChangedFilesCount(): int
    {
        return count($this->changedFiles);
    }

    /**
     * @return SplFileInfo[]
     */
    public function getChangedFiles(): array
    {
        return $this->changedFiles;
    }
}
