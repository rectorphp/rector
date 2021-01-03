<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

use Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Source\AnotherClass;

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
