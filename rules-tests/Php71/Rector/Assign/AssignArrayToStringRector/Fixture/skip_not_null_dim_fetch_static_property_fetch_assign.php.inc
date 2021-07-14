<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class SkipNotNullDimFetchStaticPropertyFetchAssign
{
    static $anotherStaticString = '';

    public function run()
    {
        self::$anotherStaticString[1] = 1;
    }
}
