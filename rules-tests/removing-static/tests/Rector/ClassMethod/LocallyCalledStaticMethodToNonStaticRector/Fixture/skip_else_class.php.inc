<?php

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

class SkipElseClass
{
    public function run()
    {
        AnotherClass::someStatic();
    }
}

class AnotherClass
{
    public static function someStatic()
    {
    }
}
