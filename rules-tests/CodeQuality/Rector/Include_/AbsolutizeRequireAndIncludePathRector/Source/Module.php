<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Source;

class Module
{
    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return include __DIR__ . './../config/module.config.php';
    }
}
