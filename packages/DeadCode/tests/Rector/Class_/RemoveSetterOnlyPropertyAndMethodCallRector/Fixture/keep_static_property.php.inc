<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

class KeepStaticProperty
{
    private static $application;

    private static function getApplication()
    {
        if (self::$application === null) {
            self::$application = new Application();
        }

        return self::$application;
    }
}
