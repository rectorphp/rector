<?php

namespace Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source\SomeClassWithConstants;

class IfConditionMethodCallWithArgClassConstantSuffixClass
{
    public function run($arg)
    {
        $obj = new self();
        if ($obj->condition(SomeClassWithConstants::class) === 1) {

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

namespace Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source\SomeClassWithConstants;

class IfConditionMethodCallWithArgClassConstantSuffixClass
{
    public function run($arg)
    {
        $obj = new self();
        $conditionSomeClassWithConstants = $obj->condition(SomeClassWithConstants::class);
        if ($conditionSomeClassWithConstants === 1) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
