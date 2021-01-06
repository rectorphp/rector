<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;

/**
 * Moves package to require section, if package is not in composer data, nothing happen, also if package is already in require section
 * @see \Rector\Composer\Tests\ValueObject\MovePackageToRequireTest
 */
final class MovePackageToRequire implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /**
     * @param string $packageName name of package to be moved (vendor/package)
     */
    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    /**
     * @inheritDoc
     */
    public function modify(array $composerData): array
    {
        $originalSection = ComposerModifier::SECTION_REQUIRE_DEV;

        if (isset($composerData[$originalSection][$this->packageName])) {
            $composerData[ComposerModifier::SECTION_REQUIRE][$this->packageName] = $composerData[$originalSection][$this->packageName];
            unset($composerData[$originalSection][$this->packageName]);
        }

        if (isset($composerData[$originalSection]) && $composerData[$originalSection] === []) {
            unset($composerData[$originalSection]);
        }

        return $composerData;
    }
}
