<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Changes package version of package which is already in composer data
 * @see \Rector\Composer\Tests\Modifier\ChangePackageVersionTest
 */
final class ChangePackageVersionModifier implements ComposerModifierInterface
{
    /**
     * @param ChangePackageVersion $composerModifierConfiguration
     */
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson
    {
        Assert::isInstanceOf($composerModifierConfiguration, ChangePackageVersion::class);

        $composerJson->changePackageVersion($composerModifierConfiguration->getPackageName(), $composerModifierConfiguration->getTargetVersion());
        return $composerJson;
    }
}
