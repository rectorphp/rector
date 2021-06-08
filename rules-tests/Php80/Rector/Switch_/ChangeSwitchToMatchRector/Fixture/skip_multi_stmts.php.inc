<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class SkipMultiStmts
{
    public function run($value)
    {
        switch ($value) {
            case 1:
                $one = 500;
                $two = 500;
                break;

            default:
                $one = 1000;
                $two = 1000;
                break;
        }
    }
}
