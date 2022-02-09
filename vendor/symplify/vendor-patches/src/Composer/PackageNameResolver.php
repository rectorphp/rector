<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Symplify\VendorPatches\Composer;

use RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220209\Symplify\SmartFileSystem\Json\JsonFileSystem;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220209\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
use RectorPrefix20220209\Symplify\VendorPatches\FileSystem\PathResolver;
/**
 * @see \Symplify\VendorPatches\Tests\Composer\PackageNameResolverTest
 */
final class PackageNameResolver
{
    /**
     * @var \Symplify\SmartFileSystem\Json\JsonFileSystem
     */
    private $jsonFileSystem;
    /**
     * @var \Symplify\VendorPatches\FileSystem\PathResolver
     */
    private $pathResolver;
    /**
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    public function __construct(\RectorPrefix20220209\Symplify\SmartFileSystem\Json\JsonFileSystem $jsonFileSystem, \RectorPrefix20220209\Symplify\VendorPatches\FileSystem\PathResolver $pathResolver, \RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard)
    {
        $this->jsonFileSystem = $jsonFileSystem;
        $this->pathResolver = $pathResolver;
        $this->fileSystemGuard = $fileSystemGuard;
    }
    public function resolveFromFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $vendorFile) : string
    {
        $packageComposerJsonFilePath = $this->getPackageComposerJsonFilePath($vendorFile);
        $composerJson = $this->jsonFileSystem->loadFilePathToJson($packageComposerJsonFilePath);
        if (!isset($composerJson['name'])) {
            throw new \RectorPrefix20220209\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        return $composerJson['name'];
    }
    private function getPackageComposerJsonFilePath(\Symplify\SmartFileSystem\SmartFileInfo $vendorFileInfo) : string
    {
        $vendorPackageDirectory = $this->pathResolver->resolveVendorDirectory($vendorFileInfo);
        $packageComposerJsonFilePath = $vendorPackageDirectory . '/composer.json';
        $this->fileSystemGuard->ensureFileExists($packageComposerJsonFilePath, __METHOD__);
        return $packageComposerJsonFilePath;
    }
}
