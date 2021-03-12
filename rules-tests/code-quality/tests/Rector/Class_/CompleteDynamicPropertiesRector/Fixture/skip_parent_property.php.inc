<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

class SomeChildClass extends SomeParentClassWithProperty
{
    public function set()
    {
        $this->value = 5;
    }
}

class SomeParentClassWithProperty
{
    public $value;
}
