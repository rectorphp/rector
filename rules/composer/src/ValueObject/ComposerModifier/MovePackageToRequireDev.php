<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Moves package to require-dev section, if package is not in composer data, nothing happen, also if package is already in require-dev section
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\MovePackageToRequireDevTest
 */
final class MovePackageToRequireDev implements ComposerModifierInterface
{
    /**
     * @var string
     */
    private $packageName;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function modify(ComposerJson $composerJson): void
    {
        $composerJson->movePackageToRequireDev($this->packageName);
    }
}
