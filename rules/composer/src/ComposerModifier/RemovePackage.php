<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerModifier;

use Rector\Composer\Rector\ComposerRector;

/**
 * Removes package from composer data
 */
final class RemovePackage implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function modify(array $composerData): array
    {
        foreach ([ComposerRector::SECTION_REQUIRE, ComposerRector::SECTION_REQUIRE_DEV] as $section) {
            unset($composerData[$section][$this->packageName]);
            if (empty($composerData[$section])) {
                unset($composerData[$section]);
            }
        }
        return $composerData;
    }
}
