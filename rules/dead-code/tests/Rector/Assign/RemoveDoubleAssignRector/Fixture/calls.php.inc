<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class Calls
{
    public function functions()
    {
        $items = [];
        $item = array_pop($items);
        $item = array_pop($items);
        $item = array_pop($items);
    }

    public function staticCalls()
    {
        $item = StaticClass::dump();
        $item = StaticClass::dump();
        $item = StaticClass::dump();
    }

    public function nonStaticCalls()
    {
        $nonStaticClass = (new NonStaticClass());
        $item = $nonStaticClass->dump();
        $item = $nonStaticClass->dump();
    }
}

class StaticClass
{
    public static function dump()
    {
        $items = [];
        return array_pop($items);
    }
}

class NonStaticClass
{
    public function dump()
    {
        $items = [];
        return array_pop($items);
    }
}
