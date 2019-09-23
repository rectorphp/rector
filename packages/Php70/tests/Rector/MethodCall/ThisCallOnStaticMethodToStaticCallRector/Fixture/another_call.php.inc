<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class AnotherCall
{
    public function __construct()
    {
    }

    public static function run()
    {
        $anotherClass = new AnotherClass;
        $anotherClass->eat();
    }
}

class AnotherClass
{
    public static function eat()
    {
    }
}
