<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SkipWhenSwitchDoesNotAssignValue
{
    public function run()
    {
        switch ($input) {
            case "a":
                $ths->a();
                break;
            case "b":
                $ths->b();
                break;
        }
    }
    public function a() {}
    public function b() {}
}
?>
