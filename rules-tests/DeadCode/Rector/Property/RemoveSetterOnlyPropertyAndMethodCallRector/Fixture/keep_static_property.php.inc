<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

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
