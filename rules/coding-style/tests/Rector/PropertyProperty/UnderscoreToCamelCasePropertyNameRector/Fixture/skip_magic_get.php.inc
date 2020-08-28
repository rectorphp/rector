<?php

namespace Rector\CodingStyle\Tests\Rector\PropertyProperty\UnderscoreToCamelCasePropertyNameRector\Fixture;

class SkipMagicGet
{
    public function get()
    {
        $this->underscore_value = 5;
    }

    public function __get($key)
    {
    }
}
