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
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackage;

final class ComposerModifierFactory
{
    /** @var AddPackageToRequireModifier */
    private $addPackageToRequireModifier;

    /** @var AddPackageToRequireDevModifier */
    private $addPackageToRequireDevModifier;

    /** @var ChangePackageVersionModifier */
    private $changePackageVersionModifier;

    /** @var MovePackageToRequireModifier */
    private $movePackageToRequireModifier;

    /** @var MovePackageToRequireDevModifier */
    private $movePackageToRequireDevModifier;

    /** @var RemovePackageModifier */
    private $removePackageModifier;

    /** @var ReplacePackageModifier */
    private $replacePackageModifier;

    public function __construct(
        AddPackageToRequireModifier $addPackageToRequireModifier,
        AddPackageToRequireDevModifier $addPackageToRequireDevModifier,
        ChangePackageVersionModifier $changePackageVersionModifier,
        MovePackageToRequireModifier $movePackageToRequireModifier,
        MovePackageToRequireDevModifier $movePackageToRequireDevModifier,
        RemovePackageModifier $removePackageModifier,
        ReplacePackageModifier $replacePackageModifier
    ) {
        $this->addPackageToRequireModifier = $addPackageToRequireModifier;
        $this->addPackageToRequireDevModifier = $addPackageToRequireDevModifier;
        $this->changePackageVersionModifier = $changePackageVersionModifier;
        $this->movePackageToRequireModifier = $movePackageToRequireModifier;
        $this->movePackageToRequireDevModifier = $movePackageToRequireDevModifier;
        $this->removePackageModifier = $removePackageModifier;
        $this->replacePackageModifier = $replacePackageModifier;
    }

    public function create(ComposerModifierConfigurationInterface $composerModifierConfiguration): ?ComposerModifierInterface
    {
        switch (get_class($composerModifierConfiguration)) {
            case AddPackageToRequire::class:
                return $this->addPackageToRequireModifier;
            case AddPackageToRequireDev::class:
                return $this->addPackageToRequireDevModifier;
            case ChangePackageVersion::class:
                return $this->changePackageVersionModifier;
            case MovePackageToRequire::class:
                return $this->movePackageToRequireModifier;
            case MovePackageToRequireDev::class:
                return $this->movePackageToRequireDevModifier;
            case RemovePackageModifier::class:
                return $this->removePackageModifier;
            case ReplacePackage::class:
                return $this->replacePackageModifier;
            default:
                return null;
        }
    }
}
