<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequireDev;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Only adds package to require-dev section, if package is already in composer data, nothing happen
 * @see \Rector\Composer\Tests\Modifier\AddPackageToRequireDevTest
 */
final class AddPackageToRequireDevModifier implements ComposerModifierInterface
{
    /**
     * @param ComposerJson $composerJson
     * @param AddPackageToRequireDev $composerModifierConfiguration
     * @return ComposerJson
     */
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson
    {
        Assert::isInstanceOf($composerModifierConfiguration, AddPackageToRequireDev::class);

        $composerJson->addRequiredDevPackage($composerModifierConfiguration->getPackageName(), $composerModifierConfiguration->getVersion()->getVersion());
        return $composerJson;
    }
}
