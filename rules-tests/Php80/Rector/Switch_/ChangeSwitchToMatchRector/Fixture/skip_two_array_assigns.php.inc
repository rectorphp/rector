<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SkipTwoArrayAssigns
{
    public function run($value)
    {
        switch ($value) {
            case 1:
                $firstCollection[] = $value;
                break;
            case 2:
                $secondCollection[] = $value;
                break;
            default:
                $secondCollection[] = 1000;
        }
    }
}
