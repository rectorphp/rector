<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackage;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Replace one package for another
 * @see \Rector\Composer\Tests\Modifier\ReplacePackageTest
 */
final class ReplacePackageModifier implements ComposerModifierInterface
{
    /**
     * @param ComposerJson $composerJson
     * @param ReplacePackage $composerModifierConfiguration
     * @return ComposerJson
     */
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson
    {
        Assert::isInstanceOf($composerModifierConfiguration, ReplacePackage::class);

        $composerJson->replacePackage($composerModifierConfiguration->getOldPackageName(), $composerModifierConfiguration->getNewPackageName(), $composerModifierConfiguration->getTargetVersion()->getVersion());
        return $composerJson;
    }
}
