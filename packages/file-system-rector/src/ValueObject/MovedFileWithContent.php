<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\ValueObject;

use Rector\FileSystemRector\Contract\MovedFileInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MovedFileWithContent implements MovedFileInterface
{
    /**
     * @var string
     */
    private $newPathname;

    /**
     * @var string
     */
    private $fileContent;

    /**
     * @var SmartFileInfo
     */
    private $oldFileInfo;

    public function __construct(SmartFileInfo $oldFileInfo, string $newPathname)
    {
        $this->oldFileInfo = $oldFileInfo;
        $this->newPathname = $newPathname;
        $this->fileContent = $oldFileInfo->getContents();
    }

    public function getOldPathname(): string
    {
        return $this->oldFileInfo->getPathname();
    }

    public function getNewPathname(): string
    {
        return $this->newPathname;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }
}
