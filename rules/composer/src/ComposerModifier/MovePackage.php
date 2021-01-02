<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerModifier;

use Rector\Composer\Rector\ComposerRector;
use Webmozart\Assert\Assert;

/**
 * Moves package to selected section, if package is not in composer data, nothing happen, also if package is already in selected section
 */
final class MovePackage implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $section;

    /**
     * @param string $packageName
     * @param string $section require or require-dev
     */
    public function __construct(string $packageName, string $section = ComposerRector::SECTION_REQUIRE_DEV)
    {
        Assert::oneOf($section, [ComposerRector::SECTION_REQUIRE, ComposerRector::SECTION_REQUIRE_DEV]);

        $this->packageName = $packageName;
        $this->section = $section;
    }

    public function modify(array $composerData): array
    {
        $originalSection = $this->section === ComposerRector::SECTION_REQUIRE ? ComposerRector::SECTION_REQUIRE_DEV : ComposerRector::SECTION_REQUIRE;

        if (isset($composerData[$originalSection][$this->packageName])) {
            $composerData[$this->section][$this->packageName] = $composerData[$originalSection][$this->packageName];
            unset($composerData[$originalSection][$this->packageName]);
        }

        if (empty($composerData[$originalSection])) {
            unset($composerData[$originalSection]);
        }

        return $composerData;
    }
}
