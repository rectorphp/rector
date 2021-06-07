<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SkipArrayAssignAdnDimFetch
{
    public function run($value)
    {
        switch ($value) {
            case 1:
                $firstCollection[] = $value;
                break;
            case 2:
                $firstCollection = $value;
                break;
            default:
                $firstCollection[] = 1000;
        }
    }
}
