<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;

/**
 * Removes package from composer data
 * @see \Rector\Composer\Tests\ValueObject\RemovePackageTest
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
        foreach ([ComposerModifier::SECTION_REQUIRE, ComposerModifier::SECTION_REQUIRE_DEV] as $section) {
            unset($composerData[$section][$this->packageName]);
            if (isset($composerData[$section]) && $composerData[$section] === []) {
                unset($composerData[$section]);
            }
        }
        return $composerData;
    }
}
