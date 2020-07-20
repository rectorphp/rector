<?php

declare(strict_types=1);

namespace Rector\Set\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Set
{
    /**
     * @var SmartFileInfo
     */
    private $fileInfo;

    public function __construct(string $filePath)
    {
        $this->fileInfo = new SmartFileInfo($filePath);
    }

    public function getFileRealPath(): string
    {
        return $this->fileInfo->getRealPath();
    }

    public function getFileInfo(): SmartFileInfo
    {
        return $this->fileInfo;
    }
}
