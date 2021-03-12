<?php

namespace Rector\Privatization\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

class SkipStaticCall
{
    public static function run()
    {

    }
}

function run()
{
    SkipStaticCall::run();
}
