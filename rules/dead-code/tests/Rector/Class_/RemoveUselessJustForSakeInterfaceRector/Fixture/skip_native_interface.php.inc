<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUselessJustForSakeInterfaceRector\Fixture;

use ArrayAccess;

class SkipNativeInterface implements ArrayAccess
{
    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
