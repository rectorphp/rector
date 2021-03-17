<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipReturnAndAssign
{
    private $assignedExpr;

    public function run($value)
    {
        switch ($value) {
            case 1:
                $this->assignedExpr = 1000;
                break;

            default:
                return 2000;
        }
    }
}
