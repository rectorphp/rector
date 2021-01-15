<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Moves package to require section, if package is not in composer data, nothing happen, also if package is already in require section
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\MovePackageToRequireTest
 */
final class MovePackageToRequire implements ComposerRectorInterface
{
    /**
     * @var string
     */
    private $packageName;

    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function refactor(ComposerJson $composerJson): void
    {
        $composerJson->movePackageToRequire($this->packageName);
    }
}
