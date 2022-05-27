<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Symplify\VendorPatches\ValueObject;

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
    public function __construct(SmartFileInfo $oldFileInfo, SmartFileInfo $newFileInfo, string $packageName)
    {
        $this->oldFileInfo = $oldFileInfo;
        $this->newFileInfo = $newFileInfo;
        $this->packageName = $packageName;
    }
    public function getOldFileInfo() : SmartFileInfo
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
    public function getNewFileInfo() : SmartFileInfo
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
