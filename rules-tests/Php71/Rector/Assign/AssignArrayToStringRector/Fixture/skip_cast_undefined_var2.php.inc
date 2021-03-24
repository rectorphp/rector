<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

class SkipCastUndefinedVar
{
    public function fun()
    {
        $array[] = 'foo';
        $array[] = 'bar';

        return $array;
    }
}

?>
