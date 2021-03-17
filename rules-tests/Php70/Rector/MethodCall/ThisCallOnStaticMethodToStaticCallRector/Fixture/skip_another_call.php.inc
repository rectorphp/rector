<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

use Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Source\AnotherClass;

class SkipAnotherCall
{
    public function __construct()
    {
    }

    public static function run()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->eat();
    }
}
