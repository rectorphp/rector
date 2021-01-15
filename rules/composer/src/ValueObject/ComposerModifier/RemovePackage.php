<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

/**
 * Removes package from composer data
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\RemovePackageTest
 */
final class RemovePackage implements ComposerModifierInterface
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @param string $packageName name of package to be removed (vendor/package)
     */
    public function __construct(string $packageName)
    {
        $this->packageName = $packageName;
    }

    public function modify(ComposerJson $composerJson): void
    {
        $composerJson->removePackage($this->packageName);
    }
}
