<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DefaultOutsideSwitch
{
    public function run($input)
    {
        $value = 0;
        switch ($input) {
            case 100:
                $value = 1000;
                break;
            case 200:
                $value = 2000;
                break;
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DefaultOutsideSwitch
{
    public function run($input)
    {
        $value = 0;
        $value = match ($input) {
            100 => 1000,
            200 => 2000,
            default => $value,
        };

        return $value;
    }
}

?>
