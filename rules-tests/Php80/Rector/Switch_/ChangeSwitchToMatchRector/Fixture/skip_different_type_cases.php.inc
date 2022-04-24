<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipDifferentTypeCases
{
    public function run()
    {
        switch(1) {
            case '1':
                $a = 1;
                break;
            case 1:
                $a = 2;
                break;
            default:
                $a = 3;
        }

        echo $a;
    }
}
