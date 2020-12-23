<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

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
-----
<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class AssignToProperty
{
    private $assignedExpr;

    public function run($value)
    {
        $this->assignedExpr = match ($value) {
            1 => 1000,
            default => 2000,
        };
    }
}

?>
