<?php

namespace Rector\CodingStyle\Tests\Rector\PropertyProperty\UnderscoreToCamelCasePropertyNameRector\Fixture;

class SkipMagicSet
{
    public function set()
    {
        $this->underscore_value = 5;
    }

    public function __set($key, $value)
    {
    }
}
