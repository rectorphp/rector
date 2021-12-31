<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\VendorPatches\Finder;

use RectorPrefix20211231\Symfony\Component\Finder\Finder;
use RectorPrefix20211231\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211231\Symplify\VendorPatches\Composer\PackageNameResolver;
use RectorPrefix20211231\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
final class OldToNewFilesFinder
{
    /**
     * @var \Symplify\SmartFileSystem\Finder\FinderSanitizer
     */
    private $finderSanitizer;
    /**
     * @var \Symplify\VendorPatches\Composer\PackageNameResolver
     */
    private $packageNameResolver;
    public function __construct(\RectorPrefix20211231\Symplify\SmartFileSystem\Finder\FinderSanitizer $finderSanitizer, \RectorPrefix20211231\Symplify\VendorPatches\Composer\PackageNameResolver $packageNameResolver)
    {
        $this->finderSanitizer = $finderSanitizer;
        $this->packageNameResolver = $packageNameResolver;
    }
    /**
     * @return OldAndNewFileInfo[]
     */
    public function find(string $directory) : array
    {
        $oldAndNewFileInfos = [];
        $oldFileInfos = $this->findSmartFileInfosInDirectory($directory);
        foreach ($oldFileInfos as $oldFileInfo) {
            $oldRealPath = $oldFileInfo->getRealPath();
            $oldStrrPos = (int) \strrpos($oldRealPath, '.old');
            if (\strlen($oldRealPath) - $oldStrrPos !== 4) {
                continue;
            }
            $newFilePath = \substr($oldRealPath, 0, $oldStrrPos);
            if (!\file_exists($newFilePath)) {
                continue;
            }
            $newFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($newFilePath);
            $packageName = $this->packageNameResolver->resolveFromFileInfo($newFileInfo);
            $oldAndNewFileInfos[] = new \RectorPrefix20211231\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo($oldFileInfo, $newFileInfo, $packageName);
        }
        return $oldAndNewFileInfos;
    }
    /**
     * @return SmartFileInfo[]
     */
    private function findSmartFileInfosInDirectory(string $directory) : array
    {
        $finder = \RectorPrefix20211231\Symfony\Component\Finder\Finder::create()->in($directory)->files()->exclude('composer/')->exclude('ocramius/')->name('*.old');
        return $this->finderSanitizer->sanitize($finder);
    }
}
