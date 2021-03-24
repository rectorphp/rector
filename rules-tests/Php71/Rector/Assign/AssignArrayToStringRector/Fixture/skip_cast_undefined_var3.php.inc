<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

class SkipCastUndefinedVar3
{
    public function fun()
    {
        $array = [];

        function () {
            $array[] = 'foo';
            $array[] = 'bar';

            return $array;
        };
    }
}

?>
