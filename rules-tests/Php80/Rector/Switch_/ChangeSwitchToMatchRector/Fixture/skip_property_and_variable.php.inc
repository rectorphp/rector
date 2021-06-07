<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SkipArrayAssignAdnDimFetch
{
    private $firstCollection;

    public function run($value)
    {
        switch ($value) {
            case 1:
                $firstCollection = $value;
                break;
            default:
                $this->firstCollection = 1000;
        }
    }
}
