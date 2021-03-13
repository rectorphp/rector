<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\Fixture;

class SkipAssignInNonExpression
{
    public function run()
    {
        if ($one = $two = 1) {

        }
    }
}

?>
