<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;

/**
 * Only adds package to require-dev section, if package is already in composer data, nothing happen
 * @see \Rector\Composer\Tests\ValueObject\AddPackageToRequireDevTest
 */
final class AddPackageToRequireDev implements ComposerModifierInterface
{
    /** @var string */
    private $packageName;

    /** @var string */
    private $version;

    /**
     * @param string $packageName name of package (vendor/package)
     * @param string $version target package version (1.2.3, ^1.2, ~1.2.3 etc.)
     */
    public function __construct(string $packageName, string $version)
    {
        $this->packageName = $packageName;
        $this->version = $version;
    }

    /**
     * @inheritDoc
     */
    public function modify(array $composerData): array
    {
        if (!isset($composerData[ComposerModifier::SECTION_REQUIRE][$this->packageName]) && !isset($composerData[ComposerModifier::SECTION_REQUIRE_DEV][$this->packageName])) {
            $composerData[ComposerModifier::SECTION_REQUIRE_DEV][$this->packageName] = $this->version;
        }

        return $composerData;
    }
}
