<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArgStringHasNumericInFirstChar
{
    public function run()
    {
        $obj = new self();
        if ($obj->condition('1a') === 1) {

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

class IfConditionMethodCallWithArgStringHasNumericInFirstChar
{
    public function run()
    {
        $obj = new self();
        $objCondition = $obj->condition('1a');
        if ($objCondition === 1) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
