<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source;

class IfConditionMethodCallWithNamespacedFunctionCall
{
    public function check($check)
    {
        if (Source\s('test')->bytesAt(0)) {
            return true;
        }
        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source;

class IfConditionMethodCallWithNamespacedFunctionCall
{
    public function check($check)
    {
        $sBytesAt = Source\s('test')->bytesAt(0);
        if ($sBytesAt) {
            return true;
        }
        return false;
    }
}

?>
