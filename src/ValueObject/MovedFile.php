<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;

final class MovedFile
{
    /**
     * @var string
     */
    private $newPath;

    /**
     * @var string
     */
    private $fileContent;

    /**
     * @var SmartFileInfo
     */
    private $oldFileInfo;

    public function __construct(SmartFileInfo $oldFileInfo, string $newPath)
    {
        $this->oldFileInfo = $oldFileInfo;
        $this->newPath = $newPath;
        $this->fileContent = $oldFileInfo->getContents();
    }

    public function getOldFileInfo(): SmartFileInfo
    {
        return $this->oldFileInfo;
    }

    public function getOldPathName(): string
    {
        return $this->oldFileInfo->getPathname();
    }

    public function getNewPath(): string
    {
        return $this->newPath;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }
}
