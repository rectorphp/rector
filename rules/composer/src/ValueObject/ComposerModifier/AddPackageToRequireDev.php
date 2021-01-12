<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\ValueObject\Version\Version;

/**
 * Only adds package to require-dev section, if package is already in composer data, nothing happen
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\AddPackageToRequireDevTest
 */
final class AddPackageToRequireDev implements ComposerModifierConfigurationInterface
{
    /** @var string */
    private $packageName;

    /** @var Version */
    private $version;

    /**
     * @param string $packageName name of package (vendor/package)
     * @param string $version target package version (1.2.3, ^1.2, ~1.2.3 etc.)
     */
    public function __construct(string $packageName, string $version)
    {
        $this->packageName = $packageName;
        $this->version = new Version($version);
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }
}
