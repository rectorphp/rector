<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

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
