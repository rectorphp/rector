<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Webmozart\Assert\Assert;

/**
 * Moves package to selected section, if package is not in composer data, nothing happen, also if package is already in selected section
 * @see \Rector\Composer\Tests\ValueObject\MovePackageTest
 */
final class MovePackage implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $section;

    /**
     * @param string $packageName name of package to be moved (vendor/package)
     * @param string $section require or require-dev
     */
    public function __construct(string $packageName, string $section = ComposerModifier::SECTION_REQUIRE_DEV)
    {
        Assert::oneOf($section, [ComposerModifier::SECTION_REQUIRE, ComposerModifier::SECTION_REQUIRE_DEV]);

        $this->packageName = $packageName;
        $this->section = $section;
    }

    /**
     * @inheritDoc
     */
    public function modify(array $composerData): array
    {
        $originalSection = $this->section === ComposerModifier::SECTION_REQUIRE ? ComposerModifier::SECTION_REQUIRE_DEV : ComposerModifier::SECTION_REQUIRE;

        if (isset($composerData[$originalSection][$this->packageName])) {
            $composerData[$this->section][$this->packageName] = $composerData[$originalSection][$this->packageName];
            unset($composerData[$originalSection][$this->packageName]);
        }

        if (isset($composerData[$originalSection]) && $composerData[$originalSection] === []) {
            unset($composerData[$originalSection]);
        }

        return $composerData;
    }
}
