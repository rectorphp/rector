<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerModifier;

use Rector\Composer\Rector\ComposerRector;

/**
 * Changes one package for another
 */
final class ChangePackage implements ComposerModifierInterface
{
    /** @var string */
    private $oldPackageName;

    /** @var string */
    private $newPackageName;

    /** @var string */
    private $targetVersion;

    public function __construct(string $oldPackageName, string $newPackageName, string $targetVersion)
    {
        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
        $this->targetVersion = $targetVersion;
    }

    public function modify(array $composerData): array
    {
        foreach ([ComposerRector::SECTION_REQUIRE, ComposerRector::SECTION_REQUIRE_DEV] as $section) {
            if (isset($composerData[$section][$this->oldPackageName])) {
                unset($composerData[$section][$this->oldPackageName]);
                $composerData[$section][$this->newPackageName] = $this->targetVersion;
            }
        }
        return $composerData;
    }
}
