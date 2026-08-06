<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use RectorPrefix202608\Composer\Semver\Semver;
use RectorPrefix202608\Nette\Utils\Strings;
use Rector\Composer\ValueObject\InstalledPackage;
use Rector\Set\Contract\SetInterface;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @api used by extensions
 *
 * @deprecated Bond the rules themselves instead, by implementing the ComposerPackageConstraintInterface. A set
 * triggered on a single major version has to be repeated for every version an upgrade passes through, while a bonded
 * rule states the exact package version its target API is available from and applies from there upwards.
 *
 * @see \Rector\VersionBonding\Contract\ComposerPackageConstraintInterface
 * @see https://github.com/rectorphp/rector-src/pull/8296
 *
 * @see \Rector\Tests\Set\ValueObject\ComposerTriggeredSetTest
 */
final class ComposerTriggeredSet implements SetInterface
{
    /**
     * @readonly
     */
    private string $groupName;
    /**
     * @readonly
     */
    private string $packageName;
    /**
     * @readonly
     */
    private string $version;
    /**
     * @readonly
     */
    private string $setFilePath;
    /**
     * @see https://regex101.com/r/ioYomu/1
     * @var string
     */
    private const PACKAGE_REGEX = '#^[a-z0-9-]+\/([a-z0-9-_]+|\*)$#';
    /**
     * A bare "10.0" version, that is turned into a "^10.0" constraint
     *
     * @see https://regex101.com/r/vTJXPU/1
     * @var string
     */
    private const BARE_VERSION_REGEX = '#^\d+(\.\d+)*$#';
    public function __construct(string $groupName, string $packageName, string $version, string $setFilePath)
    {
        $this->groupName = $groupName;
        $this->packageName = $packageName;
        $this->version = $version;
        $this->setFilePath = $setFilePath;
        Assert::regex($this->packageName, self::PACKAGE_REGEX);
        Assert::fileExists($setFilePath);
    }
    public function getGroupName(): string
    {
        return $this->groupName;
    }
    public function getSetFilePath(): string
    {
        return $this->setFilePath;
    }
    /**
     * @param array<string, InstalledPackage> $installedPackages
     */
    public function matchInstalledPackages(array $installedPackages): bool
    {
        $package = $installedPackages[$this->packageName] ?? null;
        if (!$package instanceof InstalledPackage) {
            return \false;
        }
        return Semver::satisfies($package->getVersion(), $this->resolveVersionConstraint());
    }
    public function getName(): string
    {
        return $this->packageName . ' ' . $this->version;
    }
    /**
     * A bare version means "this major version", e.g. "10.0" is "^10.0". Anything else is used as is,
     * to allow a set that spans multiple major versions, e.g. ">=10.0" or ">=10.0 <13.0".
     */
    private function resolveVersionConstraint(): string
    {
        if (Strings::match($this->version, self::BARE_VERSION_REGEX) !== null) {
            return '^' . $this->version;
        }
        return $this->version;
    }
}
