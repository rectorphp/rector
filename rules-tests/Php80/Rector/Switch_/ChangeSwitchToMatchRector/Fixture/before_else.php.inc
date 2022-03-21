<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

function f(int $param, bool $flag): string
{
    if (!$flag) {
        $value = "A";
    } else {
        switch ($param) {
            case 1:
                $value = "B";
                break;
            case 2:
                $value = "C";
                break;
            default:
                $value = "D";
                break;
        }
    }

    return $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

function f(int $param, bool $flag): string
{
    if (!$flag) {
        $value = "A";
    } else {
        $value = match ($param) {
            1 => "B",
            2 => "C",
            default => "D",
        };
    }

    return $value;
}

?>
