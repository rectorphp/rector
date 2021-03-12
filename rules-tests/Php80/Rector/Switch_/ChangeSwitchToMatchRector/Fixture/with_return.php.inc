<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class WithReturn
{
    public function run($value)
    {
        switch ($value) {
            case 1:
                return 1000;
                break;

            default:
                return 2000;
                break;
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class WithReturn
{
    public function run($value)
    {
        return match ($value) {
            1 => 1000,
            default => 2000,
        };
    }
}

?>
