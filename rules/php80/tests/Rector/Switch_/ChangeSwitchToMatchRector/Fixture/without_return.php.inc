<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class WithoutReturn
{
    public function run($value)
    {
        $action = fn ($var): void => return;
        
        switch ($value) {
            case 1:
                $action($value);
                break;

            default:
                $action($value);
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
        $action = fn ($var): void => return;
        
        match ($value) {
            1 => $action($value),
            default => $action($value),
        };
    }
}

?>
