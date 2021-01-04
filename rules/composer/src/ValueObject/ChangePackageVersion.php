<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;

/**
 * Changes package version of package which is already in composer data
 * @see \Rector\Composer\Tests\ValueObject\ChangePackageVersionTest
 */
final class ChangePackageVersion implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $targetVersion;

    /**
     * @param string $packageName name of package to be changed (vendor/package)
     * @param string $targetVersion target package version (1.2.3, ^1.2, ~1.2.3 etc.)
     */
    public function __construct(string $packageName, string $targetVersion)
    {
        $this->packageName = $packageName;
        $this->targetVersion = $targetVersion;
    }

    /**
     * @inheritDoc
     */
    public function modify(array $composerData): array
    {
        foreach ([ComposerModifier::SECTION_REQUIRE, ComposerModifier::SECTION_REQUIRE_DEV] as $section) {
            if (isset($composerData[$section][$this->packageName])) {
                $composerData[$section][$this->packageName] = $this->targetVersion;
            }
        }
        return $composerData;
    }
}
