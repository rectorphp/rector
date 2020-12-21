<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class Fixture
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
        }

        if ([] !== $values) {
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

class Fixture
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }

        foreach ($values as $value) {
            echo $value;
        }
    }
}

?>
