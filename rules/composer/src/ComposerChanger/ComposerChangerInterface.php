<?php

declare(strict_types=1);

namespace Rector\Composer\ComposerChanger;

interface ComposerChangerInterface
{
    /**
     * @param array $composerData decoded data from composer.json
     * @return array changed $composerData
     */
    public function process(array $composerData): array;
}
