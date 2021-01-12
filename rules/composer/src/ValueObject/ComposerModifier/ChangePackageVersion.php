<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\ValueObject\Version\Version;

/**
 * Changes package version of package which is already in composer data
 * @see \Rector\Composer\Tests\Modifier\ChangePackageVersionTest
 */
final class ChangePackageVersion implements ComposerModifierConfigurationInterface
{
    /** @var string */
    private $packageName;

    /** @var Version */
    private $targetVersion;

    /**
     * @param string $packageName name of package to be changed (vendor/package)
     * @param string $targetVersion target package version (1.2.3, ^1.2, ~1.2.3 etc.)
     */
    public function __construct(string $packageName, string $targetVersion)
    {
        $this->packageName = $packageName;
        $this->targetVersion = new Version($targetVersion);
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getTargetVersion(): string
    {
        return $this->targetVersion->getVersion();
    }
}
