<?php

namespace Rector\Privatization\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SkipMethodCall
{
    public static function run()
    {

    }
}

class ClassCalling
{
    public function go($skipMethodCall)
    {
        /** @var SkipMethodCall $skipMethodCall */
        $skipMethodCall->run();
    }
}

function localFunction()
{
    // just so the ClassCalling is not made abstract
    $classCalling = new ClassCalling();
}
