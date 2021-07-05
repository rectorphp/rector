<?php

declare (strict_types=1);
namespace RectorPrefix20210705\Symplify\VendorPatches\Finder;

use RectorPrefix20210705\Symfony\Component\Finder\Finder;
use RectorPrefix20210705\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210705\Symplify\VendorPatches\Composer\PackageNameResolver;
use RectorPrefix20210705\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
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
    public function __construct(\RectorPrefix20210705\Symplify\SmartFileSystem\Finder\FinderSanitizer $finderSanitizer, \RectorPrefix20210705\Symplify\VendorPatches\Composer\PackageNameResolver $packageNameResolver)
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
            $newFilePath = \rtrim($oldFileInfo->getRealPath(), '.old');
            if (!\file_exists($newFilePath)) {
                continue;
            }
            $newFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($newFilePath);
            $packageName = $this->packageNameResolver->resolveFromFileInfo($newFileInfo);
            $oldAndNewFileInfos[] = new \RectorPrefix20210705\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo($oldFileInfo, $newFileInfo, $packageName);
        }
        return $oldAndNewFileInfos;
    }
    /**
     * @return SmartFileInfo[]
     */
    private function findSmartFileInfosInDirectory(string $directory) : array
    {
        $finder = \RectorPrefix20210705\Symfony\Component\Finder\Finder::create()->in($directory)->files()->exclude('composer/')->exclude('ocramius/')->name('*.old');
        return $this->finderSanitizer->sanitize($finder);
    }
}
