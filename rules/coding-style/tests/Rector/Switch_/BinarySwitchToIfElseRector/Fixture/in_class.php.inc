<?php

namespace Rector\CodingStyle\Tests\Rector\Switch_\BinarySwitchToIfElseRector\Fixture;

final class InClass
{
    public function run($meal)
    {
        switch ($meal) {
            case 'morning-food':
                $food = 'small chicken';
                break;
            default:
                $food = 'chicken';
        }
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Switch_\BinarySwitchToIfElseRector\Fixture;

final class InClass
{
    public function run($meal)
    {
        if ($meal == 'morning-food') {
            $food = 'small chicken';
        } else {
            $food = 'chicken';
        }
    }
}

?>
