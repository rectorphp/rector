<?php

namespace Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector\Fixture;

function skip_nested($value, array $array)
{
    switch ($value) {
        case 1:
            foreach ($array as $item) {
                if ($item == 5) {
                    continue;
                }
            }
            break;
        case 2:
            echo 'Hello';
            break;
    }
}
