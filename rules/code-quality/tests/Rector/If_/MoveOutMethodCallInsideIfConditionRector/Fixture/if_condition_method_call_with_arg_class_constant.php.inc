<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArgClassConstant
{
    private const ARG1 = 1;
    private const ARG2 = 2;

    public function run($arg)
    {
        $obj = new self();
        if ($obj->condition(IfConditionMethodCallWithArg::ARG1) === 1) {

        }
        if ($obj->condition(IfConditionMethodCallWithArg::ARG2) === 2) {

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

class IfConditionMethodCallWithArgClassConstant
{
    private const ARG1 = 1;
    private const ARG2 = 2;

    public function run($arg)
    {
        $obj = new self();
        $objArg1 = $obj->condition(IfConditionMethodCallWithArg::ARG1);
        if ($objArg1 === 1) {

        }
        $objArg2 = $obj->condition(IfConditionMethodCallWithArg::ARG2);
        if ($objArg2 === 2) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
