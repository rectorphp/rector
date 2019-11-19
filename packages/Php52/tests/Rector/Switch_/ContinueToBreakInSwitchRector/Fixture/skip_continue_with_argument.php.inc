<?php

namespace Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector\Fixture;

function skip_continue_with_argument(array $array)
{
    $x = 2;
    foreach ($array as $item) {
        switch ($item) {
            case 1:
                echo "$item\n";
                continue 2;
            case 2:
                echo "$item\n";
                continue $x;
            default:
                echo "default\n";
                break;
        }

        echo "loop\n";
    }
}
