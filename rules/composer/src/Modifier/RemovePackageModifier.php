<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Removes package from composer data
 * @see \Rector\Composer\Tests\Modifier\RemovePackageTest
 */
final class RemovePackageModifier implements ComposerModifierInterface
{
    /**
     * @param RemovePackage $composerModifierConfiguration
     */
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson
    {
        Assert::isInstanceOf($composerModifierConfiguration, RemovePackage::class);

        $composerJson->removePackage($composerModifierConfiguration->getPackageName());
        return $composerJson;
    }
}
