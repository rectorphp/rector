<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class SkipValueVarNotVariable
{
    public function run()
    {
        // @see https://www.php.net/manual/en/control-structures.foreach.php#120201
        $arr = array(
            [1, 2, 3,   4],
            [14, 6, 7,  6],
            [10, 2 ,3 , 2],
        );

        foreach( $arr as list( , , $a)) {
            echo "$a\n";
        }
    }
}
