<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SkipDefined
{
    private $value;
    public function set()
    {
        $this->value = 5;
    }
}
