<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use Rector\Composer\ValueObject\InstalledPackage;
use Rector\Set\Contract\SetInterface;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @api used by extensions
 */
final class ComposerTriggeredSet implements SetInterface
{
    /**
     * @readonly
     * @var string
     */
    private $groupName;
    /**
     * @readonly
     * @var string
     */
    private $packageName;
    /**
     * @readonly
     * @var string
     */
    private $version;
    /**
     * @readonly
     * @var string
     */
    private $setFilePath;
    /**
     * @var string
     * @see https://regex101.com/r/ioYomu/1
     */
    private const PACKAGE_REGEX = '#^[a-z0-9-]+\\/([a-z0-9-_]+|\\*)$#';
    public function __construct(string $groupName, string $packageName, string $version, string $setFilePath)
    {
        $this->groupName = $groupName;
        $this->packageName = $packageName;
        $this->version = $version;
        $this->setFilePath = $setFilePath;
        Assert::regex($this->packageName, self::PACKAGE_REGEX);
        Assert::fileExists($setFilePath);
    }
    public function getGroupName() : string
    {
        return $this->groupName;
    }
    public function getSetFilePath() : string
    {
        return $this->setFilePath;
    }
    /**
     * @param InstalledPackage[] $installedPackages
     */
    public function matchInstalledPackages(array $installedPackages) : bool
    {
        foreach ($installedPackages as $installedPackage) {
            if ($installedPackage->getName() !== $this->packageName) {
                continue;
            }
            return \version_compare($installedPackage->getVersion(), $this->version) !== -1;
        }
        return \false;
    }
    public function getName() : string
    {
        return $this->packageName . ' ' . $this->version;
    }
}
