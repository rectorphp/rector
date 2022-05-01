<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\VendorPatches\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;
final class OldAndNewFileInfo
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $oldFileInfo;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $newFileInfo;
    /**
     * @var string
     */
    private $packageName;
    public function __construct(\Symplify\SmartFileSystem\SmartFileInfo $oldFileInfo, \Symplify\SmartFileSystem\SmartFileInfo $newFileInfo, string $packageName)
    {
        $this->oldFileInfo = $oldFileInfo;
        $this->newFileInfo = $newFileInfo;
        $this->packageName = $packageName;
    }
    public function getOldFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->oldFileInfo;
    }
    public function getOldFileRelativePath() : string
    {
        return $this->oldFileInfo->getRelativeFilePathFromCwd();
    }
    public function getNewFileRelativePath() : string
    {
        return $this->newFileInfo->getRelativeFilePathFromCwd();
    }
    public function getNewFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->newFileInfo;
    }
    public function isContentIdentical() : bool
    {
        return $this->newFileInfo->getContents() === $this->oldFileInfo->getContents();
    }
    public function getPackageName() : string
    {
        return $this->packageName;
    }
}
