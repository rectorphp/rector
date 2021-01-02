<?php

declare(strict_types=1);

namespace Rector\Composer\Contract\ComposerModifier;

interface ComposerModifierInterface
{
    /**
     * @param array<string,mixed> $composerData decoded data from composer.json
     * @return array<string,mixed> changed $composerData
     */
    public function modify(array $composerData): array;
}
