<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class SkipExplicitDimFetch
{
    public function run()
    {
        $anotherString = '';
        $anotherString[1] = 1;
    }
}
