<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class MirrorComment
{
    public function run(array $values)
    {
        $tmp = [];

        // put lower values into tmp
        if ($values !== []) {
            // loop to put into tmp
            foreach ($values as $value) {
                $tmp[] = strtolower($value);
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

class MirrorComment
{
    public function run(array $values)
    {
        $tmp = [];

        // put lower values into tmp
        // loop to put into tmp
        foreach ($values as $value) {
            $tmp[] = strtolower($value);
        }
    }
}

?>
