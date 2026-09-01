<?php

declare (strict_types=1);
namespace Rector\Composer;

use RectorPrefix202609\Composer\Semver\Semver;
use RectorPrefix202609\Composer\Semver\VersionParser;
use RectorPrefix202609\Nette\Utils\FileSystem;
use RectorPrefix202609\Nette\Utils\Json;
use Rector\Composer\ValueObject\InstalledPackage;
use Rector\Exception\ShouldNotHappenException;
use Rector\Skipper\FileSystem\PathNormalizer;
use UnexpectedValueException;
use RectorPrefix202609\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Composer\InstalledPackageResolverTest
 */
final class InstalledPackageResolver
{
    /**
     * @var null|string
     */
    private ?string $composerJsonFilePath = null;
    /**
     * @var string
     */
    private const ANY_VERSION_LOWER_BOUND = '0.0.0.0-dev';
    /**
     * @var null|array<string, InstalledPackage>
     */
    private ?array $resolvedInstalledPackages = null;
    /**
     * @var null|array<string, mixed>
     */
    private ?array $projectComposerJson = null;
    /**
     * @readonly
     */
    private string $projectDirectory;
    /**
     * @param null|string $composerJsonFilePath a standalone "composer.json" to read the versions from, instead of the
     *                                          installed packages; used to test rules bonded to a composer package
     */
    public function __construct(?string $projectDirectory = null, ?string $composerJsonFilePath = null)
    {
        $this->composerJsonFilePath = $composerJsonFilePath;
        // fallback to root project directory
        $this->projectDirectory = $projectDirectory ?? (string) getcwd();
        Assert::directory($this->projectDirectory);
    }
    /**
     * @api used in tests to resolve the versions from a standalone "composer.json"
     */
    public function changeComposerJsonFilePath(?string $composerJsonFilePath): void
    {
        $this->composerJsonFilePath = $composerJsonFilePath;
        // the previous file is no longer the source, drop what was read from it
        $this->resolvedInstalledPackages = null;
        $this->projectComposerJson = null;
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
        if ($this->composerJsonFilePath !== null) {
            return $this->resolvedInstalledPackages = $this->createPackagesFromConstraints();
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
        $packageConstraints = $this->resolvePackageConstraints();
        $isLibrary = $this->isLibrary();
        $installedPackages = [];
        foreach ($packages as $package) {
            $name = $package['name'];
            $version = $package['version_normalized'];
            $constraint = $packageConstraints[$name] ?? null;
            if (is_string($constraint)) {
                if ($isLibrary) {
                    // a library must stay compatible with the lowest version it declares,
                    // regardless of which one happens to be installed locally
                    $version = $this->resolveConstraintLowestVersion($constraint) ?? $version;
                } else {
                    // the "installed.json" can be outdated, e.g. after a branch switch;
                    // in such case the "composer.json" constraint has a priority
                    $version = $this->matchConstraintVersion($version, $constraint) ?? $version;
                }
            }
            $installedPackages[$name] = new InstalledPackage($name, $version);
        }
        return $installedPackages;
    }
    /**
     * A library declares a compatibility range in its "composer.json"; the version-specific rules must target the
     * lowest declared version, not the one that happens to be installed locally.
     *
     * Anything but an application counts as a library: a missing "type" or an explicit "project" is an application,
     * every other declared type ("library", "symfony-bundle", ...) is a distributed package.
     */
    private function isLibrary(): bool
    {
        $projectComposerJson = $this->loadProjectComposerJson();
        $type = $projectComposerJson['type'] ?? null;
        return is_string($type) && $type !== 'project';
    }
    /**
     * There is no vendor to read the installed versions from, so the constraints themselves are the only source
     *
     * @return array<string, InstalledPackage>
     */
    private function createPackagesFromConstraints(): array
    {
        $installedPackages = [];
        foreach ($this->resolvePackageConstraints() as $packageName => $constraint) {
            $version = $this->resolveConstraintLowestVersion($constraint);
            if ($version === null) {
                continue;
            }
            $installedPackages[$packageName] = new InstalledPackage($packageName, $version);
        }
        return $installedPackages;
    }
    /**
     * @return null|string the lowest version allowed by the constraint, if the installed version is out of it
     */
    private function matchConstraintVersion(string $installedVersion, string $constraint): ?string
    {
        try {
            if (Semver::satisfies($installedVersion, $constraint)) {
                return null;
            }
        } catch (UnexpectedValueException $exception) {
            // non-comparable version or constraint, e.g. a dev one
            return null;
        }
        return $this->resolveConstraintLowestVersion($constraint);
    }
    /**
     * @return null|string the lowest version the constraint allows, null if there is no comparable one
     */
    private function resolveConstraintLowestVersion(string $constraint): ?string
    {
        try {
            $lowestVersion = (new VersionParser())->parseConstraints($constraint)->getLowerBound()->getVersion();
        } catch (UnexpectedValueException $exception) {
            // non-comparable version or constraint, e.g. a dev one
            return null;
        }
        // the constraint allows any version, nothing to fall back to
        if ($lowestVersion === self::ANY_VERSION_LOWER_BOUND) {
            return null;
        }
        // the lower bound is a dev one, e.g. "10.5.0.0-dev" for the "^10.5" constraint
        if (substr_compare($lowestVersion, '-dev', -strlen('-dev')) === 0) {
            return (string) substr($lowestVersion, 0, -strlen('-dev'));
        }
        return $lowestVersion;
    }
    /**
     * @return array<string, string> package name to the "composer.json" version constraint
     */
    private function resolvePackageConstraints(): array
    {
        $projectComposerJson = $this->loadProjectComposerJson();
        $packageConstraints = [];
        foreach (['require', 'require-dev'] as $section) {
            $requiredPackages = $projectComposerJson[$section] ?? null;
            if (!is_array($requiredPackages)) {
                continue;
            }
            foreach ($requiredPackages as $packageName => $constraint) {
                if (is_string($packageName) && is_string($constraint)) {
                    $packageConstraints[$packageName] = $constraint;
                }
            }
        }
        return $packageConstraints;
    }
    /**
     * @return array<string, mixed>
     */
    private function loadProjectComposerJson(): array
    {
        if ($this->projectComposerJson !== null) {
            return $this->projectComposerJson;
        }
        $projectComposerJsonFilePath = $this->composerJsonFilePath ?? $this->projectDirectory . '/composer.json';
        if (!file_exists($projectComposerJsonFilePath)) {
            return $this->projectComposerJson = [];
        }
        $projectComposerContents = FileSystem::read($projectComposerJsonFilePath);
        $projectComposerJson = Json::decode($projectComposerContents, \true);
        return $this->projectComposerJson = is_array($projectComposerJson) ? $projectComposerJson : [];
    }
    private function resolveVendorDir(): string
    {
        $projectComposerJson = $this->loadProjectComposerJson();
        if (isset($projectComposerJson['config']['vendor-dir']) && is_string($projectComposerJson['config']['vendor-dir'])) {
            $realPathVendorDir = realpath($projectComposerJson['config']['vendor-dir']) ?: '';
            $normalizedRealPathVendorDir = PathNormalizer::normalize($realPathVendorDir);
            $normalizedVendorDir = PathNormalizer::normalize($projectComposerJson['config']['vendor-dir']);
            return $normalizedRealPathVendorDir === $normalizedVendorDir ? $projectComposerJson['config']['vendor-dir'] : $this->projectDirectory . '/' . $projectComposerJson['config']['vendor-dir'];
        }
        return $this->projectDirectory . '/vendor';
    }
}
