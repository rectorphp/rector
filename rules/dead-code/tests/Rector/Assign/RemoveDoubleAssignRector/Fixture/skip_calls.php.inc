<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class SkipCalls
{
    public function functions()
    {
        $items = [];
        $item = array_pop($items);
        $item = array_pop($items);
        $item = array_pop($items);
        $item = null;
    }

    public function staticCalls()
    {
        $item = StaticClass::dump();
        $item = StaticClass::dump();
        $item = StaticClass::dump();
        $item = null;
    }

    public function nonStaticCalls()
    {
        $nonStaticClass = (new NonStaticClass());
        $item = $nonStaticClass->dump();
        $item = $nonStaticClass->dump();
        $item = $nonStaticClass->dump();
        $item = null;
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
