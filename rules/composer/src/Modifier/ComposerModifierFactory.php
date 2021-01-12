<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequireDev;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequire;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequireDev;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackage;

final class ComposerModifierFactory
{
    /** @var array<string, ComposerModifierInterface> */
    private $composerModifiers = [];

    public function __construct(
        AddPackageToRequireModifier $addPackageToRequireModifier,
        AddPackageToRequireDevModifier $addPackageToRequireDevModifier,
        ChangePackageVersionModifier $changePackageVersionModifier,
        MovePackageToRequireModifier $movePackageToRequireModifier,
        MovePackageToRequireDevModifier $movePackageToRequireDevModifier,
        RemovePackageModifier $removePackageModifier,
        ReplacePackageModifier $replacePackageModifier
    ) {
        $this->composerModifiers = [
            AddPackageToRequire::class => $addPackageToRequireModifier,
            AddPackageToRequireDev::class => $addPackageToRequireDevModifier,
            ChangePackageVersion::class => $changePackageVersionModifier,
            MovePackageToRequire::class => $movePackageToRequireModifier,
            MovePackageToRequireDev::class => $movePackageToRequireDevModifier,
            RemovePackage::class => $removePackageModifier,
            ReplacePackage::class => $replacePackageModifier,
        ];
    }

    public function create(ComposerModifierConfigurationInterface $composerModifierConfiguration): ?ComposerModifierInterface
    {
        return $this->composerModifiers[get_class($composerModifierConfiguration)] ?? null;
    }
}
