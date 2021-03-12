<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SkipMagic
{
    public function set()
    {
        $this->value = 5;
    }

    public function __set($key, $value)
    {
    }
}
