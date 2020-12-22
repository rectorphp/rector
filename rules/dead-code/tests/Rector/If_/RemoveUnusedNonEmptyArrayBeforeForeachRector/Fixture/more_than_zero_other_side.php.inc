<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class MoreThanZeroOtherSide
{
    public function run()
    {
        $values = [];
        if (0 < count($values)) {
            foreach ($values as $value) {
                echo $value;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class MoreThanZeroOtherSide
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }
    }
}

?>
