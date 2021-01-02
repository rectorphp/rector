<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerModifier;

interface ComposerModifierInterface
{
    /**
     * @param array $composerData decoded data from composer.json
     * @return array changed $composerData
     */
    public function modify(array $composerData): array;
}
