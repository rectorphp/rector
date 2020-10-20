<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArg
{
    public function run($arg)
    {
        $obj = new self();
        if ($obj->condition($arg + 1) === 1) {

        }
        if ($obj->condition($arg + 2) === 2) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArg
{
    public function run($arg)
    {
        $obj = new self();
        $objCondition = $obj->condition($arg + 1);
        if ($objCondition === 1) {

        }
        $objCondition = $obj->condition($arg + 2);
        if ($objCondition === 2) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
