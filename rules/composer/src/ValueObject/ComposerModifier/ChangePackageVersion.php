<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\Version\Version;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Changes package version of package which is already in composer data
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\ChangePackageVersionTest
 */
final class ChangePackageVersion implements ComposerModifierInterface
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

    /**
     * @inheritDoc
     */
    public function modify(ComposerJson $composerJson): ComposerJson
    {
        $composerJson->changePackageVersion($this->packageName, $this->targetVersion->getVersion());
        return $composerJson;
    }
}
