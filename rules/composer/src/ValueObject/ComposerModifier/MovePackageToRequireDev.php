<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;

/**
 * Moves package to require-dev section, if package is not in composer data, nothing happen, also if package is already in require-dev section
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\MovePackageToRequireDevTest
 */
final class MovePackageToRequireDev implements ComposerModifierConfigurationInterface
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

    public function getPackageName(): string
    {
        return $this->packageName;
    }
}
