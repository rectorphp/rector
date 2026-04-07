<?php

declare (strict_types=1);
namespace Rector\Composer;

use RectorPrefix202604\Nette\Utils\FileSystem;
use RectorPrefix202604\Nette\Utils\Json;
use Rector\Composer\ValueObject\InstalledPackage;
use Rector\Exception\ShouldNotHappenException;
use Rector\Skipper\FileSystem\PathNormalizer;
use RectorPrefix202604\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\InstalledPackageResolverTest
 */
final class InstalledPackageResolver
{
    /**
     * @readonly
     */
    private ?string $projectDirectory = null;
    /**
     * @var null|array<string, InstalledPackage>
     */
    private ?array $resolvedInstalledPackages = null;
    public function __construct(?string $projectDirectory = null)
    {
        $this->projectDirectory = $projectDirectory;
        // fallback to root project directory
        if ($projectDirectory === null) {
            $projectDirectory = getcwd();
        }
        Assert::directory($projectDirectory);
    }
    /**
     * @return array<string, InstalledPackage>
     */
    public function resolve(): array
    {
        // already cached, even only empty array
        if ($this->resolvedInstalledPackages !== null) {
            return $this->resolvedInstalledPackages;
        }
        $installedPackagesFilePath = $this->resolveVendorDir() . '/composer/installed.json';
        if (!file_exists($installedPackagesFilePath)) {
            throw new ShouldNotHappenException('The installed package json not found. Make sure you run `composer update` and the "vendor/composer/installed.json" file exists');
        }
        $installedPackageFileContents = FileSystem::read($installedPackagesFilePath);
        $installedPackagesFilePath = Json::decode($installedPackageFileContents, \true);
        $installedPackages = $this->createInstalledPackages($installedPackagesFilePath['packages']);
        $this->resolvedInstalledPackages = $installedPackages;
        return $installedPackages;
    }
    public function resolvePackageVersion(string $packageName): ?string
    {
        $package = $this->resolve()[$packageName] ?? null;
        if (!$package instanceof InstalledPackage) {
            return null;
        }
        return $package->getVersion();
    }
    /**
     * @param mixed[] $packages
     * @return array<string, InstalledPackage>
     */
    private function createInstalledPackages(array $packages): array
    {
        $installedPackages = [];
        foreach ($packages as $package) {
            $name = $package['name'];
            $installedPackages[$name] = new InstalledPackage($name, $package['version_normalized']);
        }
        return $installedPackages;
    }
    private function resolveVendorDir(): string
    {
        $projectComposerJsonFilePath = $this->projectDirectory . '/composer.json';
        if (\file_exists($projectComposerJsonFilePath)) {
            $projectComposerContents = FileSystem::read($projectComposerJsonFilePath);
            $projectComposerJson = Json::decode($projectComposerContents, \true);
            if (isset($projectComposerJson['config']['vendor-dir']) && is_string($projectComposerJson['config']['vendor-dir'])) {
                $realPathVendorDir = realpath($projectComposerJson['config']['vendor-dir']) ?: '';
                $normalizedRealPathVendorDir = PathNormalizer::normalize($realPathVendorDir);
                $normalizedVendorDir = PathNormalizer::normalize($projectComposerJson['config']['vendor-dir']);
                return $normalizedRealPathVendorDir === $normalizedVendorDir ? $projectComposerJson['config']['vendor-dir'] : $this->projectDirectory . '/' . $projectComposerJson['config']['vendor-dir'];
            }
        }
        return $this->projectDirectory . '/vendor';
    }
}
