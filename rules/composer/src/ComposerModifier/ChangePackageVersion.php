<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerModifier;

use Rector\Composer\Rector\ComposerRector;

/**
 * Changes package version of package which is already in composer data
 */
final class ChangePackageVersion implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $targetVersion;

    public function __construct(string $packageName, string $targetVersion)
    {
        $this->packageName = $packageName;
        $this->targetVersion = $targetVersion;
    }

    public function modify(array $composerData): array
    {
        foreach ([ComposerRector::SECTION_REQUIRE, ComposerRector::SECTION_REQUIRE_DEV] as $section) {
            if (isset($composerData[$section][$this->packageName])) {
                $composerData[$section][$this->packageName] = $this->targetVersion;
            }
        }
        return $composerData;
    }
}
