<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SkipDefined
{
    private $value;
    public function set()
    {
        $this->value = 5;
    }
}
