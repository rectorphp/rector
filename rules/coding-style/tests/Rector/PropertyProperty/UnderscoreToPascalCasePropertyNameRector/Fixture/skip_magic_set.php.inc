<?php

namespace Rector\CodingStyle\Tests\Rector\PropertyProperty\UnderscoreToPascalCasePropertyNameRector\Fixture;

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
