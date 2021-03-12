<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

abstract class ParentStatic
{
    public static function doWork()
    {
    }
}

class ChildStatic extends ParentStatic
{
}

class EndUser
{
    public function run()
    {
        ChildStatic::doWork();
    }
}
