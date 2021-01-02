<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Webmozart\Assert\Assert;

/**
 * Only adds package to selected section, if package is already in composer data, nothing happen
 */
final class AddPackage implements ComposerModifierInterface
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
    public function __construct(string $packageName, string $version, string $section = ComposerModifier::SECTION_REQUIRE)
    {
        Assert::oneOf($section, [ComposerModifier::SECTION_REQUIRE, ComposerModifier::SECTION_REQUIRE_DEV]);

        $this->packageName = $packageName;
        $this->version = $version;
        $this->section = $section;
    }

    public function modify(array $composerData): array
    {
        if (!isset($composerData[ComposerModifier::SECTION_REQUIRE][$this->packageName]) && !isset($composerData[ComposerModifier::SECTION_REQUIRE_DEV][$this->packageName])) {
            $composerData[$this->section][$this->packageName] = $this->version;
        }

        return $composerData;
    }
}
