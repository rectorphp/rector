<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\FixtureSkipped;

class AssignToProperty
{
    private $assignedExpr;

    public function run($value)
    {
        switch ($value) {
            case 1:
                $this->assignedExpr = 1000;
                break;

            default:
                $this->assignedExpr = 2000;
                break;
        }
    }
}

?>
