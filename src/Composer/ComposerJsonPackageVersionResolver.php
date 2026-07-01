<?php

declare (strict_types=1);
namespace Rector\Composer;

use RectorPrefix202607\Composer\Semver\VersionParser;
use Rector\FileSystem\JsonFileSystem;
use Rector\Skipper\FileSystem\PathNormalizer;
use UnexpectedValueException;
/**
 * @see \Rector\Tests\Composer\ComposerJsonPackageVersionResolverTest
 */
final class ComposerJsonPackageVersionResolver
{
    /**
     * @var null|array<string, string>
     */
    private ?array $packageVersionConstraints = null;
    /**
     * @var array<string, bool>
     */
    private array $packageMultiMajorVersions = [];
    /**
     * @var array<string, null|string>
     */
    private array $packageNamesByFilePath = [];
    /**
     * @readonly
     */
    private string $composerJsonFilePath;
    public function __construct(?string $composerJsonFilePath = null)
    {
        $this->composerJsonFilePath = $composerJsonFilePath ?? getcwd() . '/composer.json';
    }
    public function hasPackageMultiMajorVersions(string $filePath): bool
    {
        $packageName = $this->resolvePackageName($filePath);
        if ($packageName === null) {
            return \false;
        }
        return $this->packageMultiMajorVersions[$packageName] ??= $this->resolveHasPackageMultiMajorVersions($packageName);
    }
    private function resolveHasPackageMultiMajorVersions(string $packageName): bool
    {
        $versionConstraint = $this->resolvePackageVersionConstraints()[$packageName] ?? null;
        if ($versionConstraint === null) {
            return \false;
        }
        $versionConstraintParts = preg_split('#\s*\|\|?\s*#', $versionConstraint);
        if ($versionConstraintParts === \false || count($versionConstraintParts) < 2) {
            return \false;
        }
        $majorVersions = [];
        $versionParser = new VersionParser();
        foreach ($versionConstraintParts as $versionConstraintPart) {
            if (preg_match('#\d+#', $versionConstraintPart) !== 1) {
                continue;
            }
            try {
                $constraint = $versionParser->parseConstraints($versionConstraintPart);
            } catch (UnexpectedValueException $exception) {
                continue;
            }
            $lowerMajorVersion = $this->resolveMajorVersion($constraint->getLowerBound()->getVersion());
            if ($lowerMajorVersion === null) {
                continue;
            }
            $majorVersions[$lowerMajorVersion] = \true;
        }
        return count($majorVersions) > 1;
    }
    private function resolvePackageName(string $filePath): ?string
    {
        if (array_key_exists($filePath, $this->packageNamesByFilePath)) {
            return $this->packageNamesByFilePath[$filePath];
        }
        $normalizedFilePath = PathNormalizer::normalize($filePath);
        $vendorPosition = strpos($normalizedFilePath, '/vendor/');
        if ($vendorPosition === \false) {
            return $this->packageNamesByFilePath[$filePath] = null;
        }
        $vendorRelativePath = (string) substr($normalizedFilePath, $vendorPosition + strlen('/vendor/'));
        $pathParts = explode('/', $vendorRelativePath);
        if (count($pathParts) < 2) {
            return $this->packageNamesByFilePath[$filePath] = null;
        }
        return $this->packageNamesByFilePath[$filePath] = $pathParts[0] . '/' . $pathParts[1];
    }
    /**
     * @return array<string, string>
     */
    private function resolvePackageVersionConstraints(): array
    {
        if ($this->packageVersionConstraints !== null) {
            return $this->packageVersionConstraints;
        }
        if (!file_exists($this->composerJsonFilePath)) {
            return $this->packageVersionConstraints = [];
        }
        $composerJson = JsonFileSystem::readFilePath($this->composerJsonFilePath);
        $packageVersionConstraints = [];
        foreach (['require', 'require-dev'] as $requireKey) {
            $requires = $composerJson[$requireKey] ?? [];
            if (!is_array($requires)) {
                continue;
            }
            foreach ($requires as $packageName => $versionConstraint) {
                if (!is_string($packageName)) {
                    continue;
                }
                if (!is_string($versionConstraint)) {
                    continue;
                }
                $packageVersionConstraints[$packageName] = $versionConstraint;
            }
        }
        return $this->packageVersionConstraints = $packageVersionConstraints;
    }
    private function resolveMajorVersion(string $version): ?int
    {
        $match = preg_match('#^(\d+)\.#', $version, $matches);
        if ($match !== 1) {
            return null;
        }
        return (int) $matches[1];
    }
}
