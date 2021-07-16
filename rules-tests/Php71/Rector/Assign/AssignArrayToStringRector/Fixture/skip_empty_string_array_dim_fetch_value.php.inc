<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class SkipEmptyStringArrayDimFetchValue
{
    public function run()
    {
        // a
        $r = [];
        // b
        $r[] = '';
    }
}

?>
