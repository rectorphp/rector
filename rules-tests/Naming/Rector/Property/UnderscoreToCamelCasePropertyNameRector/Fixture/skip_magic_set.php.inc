<?php

namespace Rector\Tests\Naming\Rector\Property\UnderscoreToCamelCasePropertyNameRector\Fixture;

class SkipMagicSet
{
    private $underscore_value;

    public function set()
    {
        $this->underscore_value = 5;
    }

    public function __set($key, $value)
    {
    }
}
