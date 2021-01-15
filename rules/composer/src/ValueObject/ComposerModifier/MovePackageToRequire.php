<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Moves package to require section, if package is not in composer data, nothing happen, also if package is already in require section
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\MovePackageToRequireTest
 */
final class MovePackageToRequire implements ComposerModifierInterface
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @param string $packageName name of package to be moved (vendor/package)
     */
    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function modify(ComposerJson $composerJson): void
    {
        $composerJson->movePackageToRequire($this->packageName);
    }
}
