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

    /**
     * @param string $oldPackageName name of package to be replaced (vendor1/package1)
     * @param string $newPackageName new name of package (vendor2/package2)
     * @param string $targetVersion target package version (1.2.3, ^1.2, ~1.2.3 etc.)
     */
    public function __construct(string $oldPackageName, string $newPackageName, string $targetVersion)
    {
        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
        $this->targetVersion = $targetVersion;
    }

    /**
     * @inheritDoc
     */
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
