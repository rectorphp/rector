<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector\Fixture;

class SkipAssignInNonExpression
{
    public function run()
    {
        if ($one = $two = 1) {

        }
    }
}

?>
