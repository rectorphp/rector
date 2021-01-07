<?php

declare(strict_types=1);

namespace Rector\Composer\Contract\ComposerModifier;

use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

interface ComposerModifierInterface
{
    /**
     * @param ComposerJson $composerData decoded data from composer.json
     * @return ComposerJson changed $composerData
     */
    public function modify(ComposerJson $composerData): ComposerJson;
}
