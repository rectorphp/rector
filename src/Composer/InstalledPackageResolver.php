<?php

declare (strict_types=1);
namespace Rector\Composer;

use RectorPrefix202407\Nette\Utils\FileSystem;
use RectorPrefix202407\Nette\Utils\Json;
use Rector\Composer\ValueObject\InstalledPackage;
use Rector\Exception\ShouldNotHappenException;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\InstalledPackageResolverTest
 */
final class InstalledPackageResolver
{
    /**
     * @var array<string, InstalledPackage[]>
     */
    private $resolvedInstalledPackages = [];
    /**
     * @return InstalledPackage[]
     */
    public function resolve(string $projectDirectory) : array
    {
        // cache
        if (isset($this->resolvedInstalledPackages[$projectDirectory])) {
            return $this->resolvedInstalledPackages[$projectDirectory];
        }
        Assert::directory($projectDirectory);
        $installedPackagesFilePath = $projectDirectory . '/vendor/composer/installed.json';
        if (!\file_exists($installedPackagesFilePath)) {
            throw new ShouldNotHappenException('The installed package json not found. Make sure you run `composer update` and "vendor/composer/installed.json" file exists');
        }
        $installedPackageFileContents = FileSystem::read($installedPackagesFilePath);
        $installedPackagesFilePath = Json::decode($installedPackageFileContents, \true);
        $installedPackages = [];
        foreach ($installedPackagesFilePath['packages'] as $installedPackage) {
            $installedPackages[] = new InstalledPackage($installedPackage['name'], $installedPackage['version_normalized']);
        }
        $this->resolvedInstalledPackages[$projectDirectory] = $installedPackages;
        return $installedPackages;
    }
}
