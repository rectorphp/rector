<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

final class SkipStaticToDynamic extends BeLocalClass
{
    public static function run()
    {
        static::assertThis();
    }
}
