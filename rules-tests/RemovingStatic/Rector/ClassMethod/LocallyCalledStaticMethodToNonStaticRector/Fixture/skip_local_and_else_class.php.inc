<?php

namespace Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

class SkipLocalAndElseClass
{
    public function run()
    {
        YetAnotherClass::someStatic();
    }
}

class YetAnotherClass
{
    public static function someStatic()
    {
        self::callHere();
    }

    public function callHere()
    {
    }
}
