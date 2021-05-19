<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use RectorPrefix20210519\Symplify\EasyTesting\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FilesFinder
{
    /**
     * @var int
     */
    private const MAX_DIRECTORY_LEVELS_UP = 6;
    public function findExtEmConfRelativeFromGivenFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->findFileRelativeFromGivenFileInfo($fileInfo, 'ext_emconf.php');
    }
    private function findFileRelativeFromGivenFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo, string $filename) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        // special case for tests
        if (\RectorPrefix20210519\Symplify\EasyTesting\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return $fileInfo;
        }
        $currentDirectory = \dirname($fileInfo->getRealPath());
        $smartFileInfo = $this->createSmartFileInfoIfFileExistsInCurrentDirectory($currentDirectory, $filename);
        if (null !== $smartFileInfo) {
            return $smartFileInfo;
        }
        // Test some levels up.
        $currentDirectoryLevel = 1;
        while ($currentDirectory = \dirname($fileInfo->getPath(), $currentDirectoryLevel)) {
            $smartFileInfo = $this->createSmartFileInfoIfFileExistsInCurrentDirectory($currentDirectory, $filename);
            if (null !== $smartFileInfo) {
                return $smartFileInfo;
            }
            if ($currentDirectoryLevel > self::MAX_DIRECTORY_LEVELS_UP) {
                break;
            }
            $currentDirectoryLevel++;
        }
        return null;
    }
    private function createSmartFileInfoIfFileExistsInCurrentDirectory(string $currentDirectory, string $filename) : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        $filePath = \sprintf('%s/%s', $currentDirectory, $filename);
        if (\is_file($filePath)) {
            return new \Symplify\SmartFileSystem\SmartFileInfo($filePath);
        }
        return null;
    }
}
