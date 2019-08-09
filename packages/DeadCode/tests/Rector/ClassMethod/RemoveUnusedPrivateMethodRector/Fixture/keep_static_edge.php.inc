<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use stdClass;

final class KeepStaticEdge
{
    public static function run()
    {
        return self::iAmUsed();
    }

    private static function iAmUsed(): stdClass
    {
    }
}
