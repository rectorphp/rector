<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source\SomeClassWithConstants;

class IfConditionMethodCallWithArgClassConstant
{
    public function run($arg)
    {
        $obj = new self();
        if ($obj->condition(SomeClassWithConstants::ARG1) === 1) {

        }
        if ($obj->condition(SomeClassWithConstants::ARG2) === 2) {

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

use Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source\SomeClassWithConstants;

class IfConditionMethodCallWithArgClassConstant
{
    public function run($arg)
    {
        $obj = new self();
        $arg1 = $obj->condition(SomeClassWithConstants::ARG1);
        if ($arg1 === 1) {

        }
        $arg2 = $obj->condition(SomeClassWithConstants::ARG2);
        if ($arg2 === 2) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
