<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Only adds package to require section, if package is already in composer data, nothing happen
 * @see \Rector\Composer\Tests\Modifier\AddPackageToRequireTest
 */
final class AddPackageToRequireModifier implements ComposerModifierInterface
{
    /**
     * @param AddPackageToRequire $composerModifierConfiguration
     */
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson
    {
        Assert::isInstanceOf($composerModifierConfiguration, AddPackageToRequire::class);

        $composerJson->addRequiredPackage($composerModifierConfiguration->getPackageName(), $composerModifierConfiguration->getVersion()->getVersion());
        return $composerJson;
    }
}
