<?php

namespace Rector\Tests\Privatization\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector\Fixture;

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
