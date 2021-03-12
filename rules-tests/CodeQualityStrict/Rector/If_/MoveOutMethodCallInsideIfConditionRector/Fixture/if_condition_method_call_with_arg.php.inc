<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArg
{
    public function run($arg)
    {
        $obj = new self();
        if ($obj->condition($arg + 1) === 1) {

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

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArg
{
    public function run($arg)
    {
        $obj = new self();
        $objCondition = $obj->condition($arg + 1);
        if ($objCondition === 1) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
