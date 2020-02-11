<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Source;

class MagicParent
{
    public function __set($key, $value)
    {
    }

    public function __get($key)
    {
    }
}
