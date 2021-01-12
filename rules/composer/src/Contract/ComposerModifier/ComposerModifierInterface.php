<?php

declare(strict_types=1);

namespace Rector\Composer\Contract\ComposerModifier;

use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

interface ComposerModifierInterface
{
    public function modify(ComposerJson $composerJson, ComposerModifierConfigurationInterface $composerModifierConfiguration): ComposerJson;
}
