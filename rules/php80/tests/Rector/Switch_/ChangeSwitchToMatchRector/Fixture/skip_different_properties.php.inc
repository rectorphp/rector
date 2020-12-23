<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipDifferentProperties
{
    private $assignedExpr;

    private $another;

    public function run($value)
    {
        switch ($value) {
            case 1:
                $this->assignedExpr = 1000;
                break;

            default:
                $this->another = 2000;
                break;
        }
    }
}
