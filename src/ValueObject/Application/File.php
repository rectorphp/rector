<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\SmartFileSystem\SmartFileInfo;

final class File
{
    /**
     * @var SmartFileInfo
     */
    private $smartFileInfo;

    /**
     * @var string
     */
    private $fileContent;

    /**
     * @var bool
     */
    private $hasChanged = false;

    /**
     * @var string
     */
    private $originalFileContent;

    /**
     * @var FileDiff|null
     */
    private $fileDiff;

    public function __construct(SmartFileInfo $smartFileInfo, string $fileContent)
    {
        $this->smartFileInfo = $smartFileInfo;
        $this->fileContent = $fileContent;
        $this->originalFileContent = $fileContent;
    }

    public function getSmartFileInfo(): SmartFileInfo
    {
        return $this->smartFileInfo;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }

    public function changeFileContent(string $newFileContent): void
    {
        if ($this->fileContent === $newFileContent) {
            return;
        }

        $this->fileContent = $newFileContent;
        $this->hasChanged = true;
    }

    public function getOriginalFileContent(): string
    {
        return $this->originalFileContent;
    }

    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }

    public function setFileDiff(FileDiff $fileDiff): void
    {
        $this->fileDiff = $fileDiff;
    }

    public function getFileDiff(): ?FileDiff
    {
        return $this->fileDiff;
    }
}
