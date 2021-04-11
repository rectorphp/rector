<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

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
     * @var \Rector\Core\ValueObject\Reporting\FileDiff|null
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

    public function setFileDiff(\Rector\Core\ValueObject\Reporting\FileDiff $fileDiff): void
    {
        $this->fileDiff = $fileDiff;
    }

    public function getFileDiff(): ?\Rector\Core\ValueObject\Reporting\FileDiff
    {
        return $this->fileDiff;
    }
}
