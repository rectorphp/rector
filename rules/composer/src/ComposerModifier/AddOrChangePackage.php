<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerModifier;

use Rector\Composer\Rector\ComposerRector;
use Webmozart\Assert\Assert;

/**
 * Adds package to selected section, if package is already in composer data, package is updated (version and/or section)
 */
final class AddOrChangePackage implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $version;

    /** @var string */
    private $section;

    /**
     * @param string $packageName
     * @param string $version
     * @param string $section require or require-dev
     */
    public function __construct(string $packageName, string $version, string $section = ComposerRector::SECTION_REQUIRE)
    {
        Assert::oneOf($section, [ComposerRector::SECTION_REQUIRE, ComposerRector::SECTION_REQUIRE_DEV]);

        $this->packageName = $packageName;
        $this->version = $version;
        $this->section = $section;
    }

    public function modify(array $composerData): array
    {
        $composerData[$this->section][$this->packageName] = $this->version;
        $originalSection = $this->section === ComposerRector::SECTION_REQUIRE ? ComposerRector::SECTION_REQUIRE_DEV : ComposerRector::SECTION_REQUIRE;

        if (isset($composerData[$originalSection][$this->packageName])) {
            unset($composerData[$originalSection][$this->packageName]);
        }

        if (empty($composerData[$originalSection])) {
            unset($composerData[$originalSection]);
        }

        return $composerData;
    }
}
