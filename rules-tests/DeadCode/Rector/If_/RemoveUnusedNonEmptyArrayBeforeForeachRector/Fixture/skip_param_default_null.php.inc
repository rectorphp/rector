<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\Fixture;

function run(array $values = null) {
    if (!empty($values)) {
        foreach ($values as $value) {
            echo $value;
        }
    }
}

?>
