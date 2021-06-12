<?php

namespace Rector\Tests\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector\Fixture;

final class DeepNestedBreak
{
    public function run($meal)
    {
        while (true) {
            while (true) {
                switch ($meal) {
                    case 'morning-food':
                        $food = 'small chicken';
                        break 3;
                    default:
                        $food = 'chicken';
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector\Fixture;

final class DeepNestedBreak
{
    public function run($meal)
    {
        while (true) {
            while (true) {
                if ($meal == 'morning-food') {
                    $food = 'small chicken';
                    break 2;
                } else {
                    $food = 'chicken';
                }
            }
        }
    }
}

?>
