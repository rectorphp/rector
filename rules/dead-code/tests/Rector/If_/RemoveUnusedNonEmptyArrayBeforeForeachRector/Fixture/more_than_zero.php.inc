<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class MoreThanZero
{
    public function run()
    {
        $values = [];
        if (count($values) > 0) {
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

class MoreThanZero
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
