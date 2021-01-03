<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;

/**
 * Changes one package for another
 * @see \Rector\Composer\Tests\ValueObject\ChangePackageTest
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
        foreach ([ComposerModifier::SECTION_REQUIRE, ComposerModifier::SECTION_REQUIRE_DEV] as $section) {
            if (isset($composerData[$section][$this->oldPackageName])) {
                unset($composerData[$section][$this->oldPackageName]);
                $composerData[$section][$this->newPackageName] = $this->targetVersion;
            }
        }
        return $composerData;
    }
}
