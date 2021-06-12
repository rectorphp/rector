<?php

namespace Rector\Tests\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector\Fixture;

final class NestedBreak
{
    public function run($meal)
    {
        while (true) {
            switch ($meal) {
                case 'morning-food':
                    $food = 'small chicken';
                    break 2;
                default:
                    $food = 'chicken';
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector\Fixture;

final class NestedBreak
{
    public function run($meal)
    {
        while (true) {
            if ($meal == 'morning-food') {
                $food = 'small chicken';
                break;
            } else {
                $food = 'chicken';
            }
        }
    }
}

?>
