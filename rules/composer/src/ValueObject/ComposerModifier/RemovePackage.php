<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Removes package from composer data
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\RemovePackageTest
 */
final class RemovePackage implements ComposerRectorInterface
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
        $composerJson->removePackage($this->packageName);
    }
}
