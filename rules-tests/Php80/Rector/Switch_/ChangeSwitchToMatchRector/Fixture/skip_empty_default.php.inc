<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipEmptyDefault
{
    public function run($value)
    {
        switch ($value) {
            case 'a':
                $result = 'A';
                break;
            default:
        }
    }
}
