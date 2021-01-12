<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Moves package to require section, if package is not in composer data, nothing happen, also if package is already in require section
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\MovePackageToRequireTest
 */
final class MovePackageToRequireModifier implements ComposerModifierInterface
{
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson
    {
        Assert::isInstanceOf($composerModifierConfiguration, MovePackageToRequireModifier::class);

        $composerJson->movePackageToRequire($composerModifierConfiguration->getPackageName());
        return $composerJson;
    }
}
