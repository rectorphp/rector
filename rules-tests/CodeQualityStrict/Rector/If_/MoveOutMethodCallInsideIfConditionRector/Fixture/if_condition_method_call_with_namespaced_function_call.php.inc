<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use function Symfony\Component\String\s;

class IfConditionMethodCallWithNamespacedFunctionCall
{
    public function check($check)
    {
        if (s('test')->bytesAt(0)) {
            return true;
        }
        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use function Symfony\Component\String\s;

class IfConditionMethodCallWithNamespacedFunctionCall
{
    public function check($check)
    {
        $sBytesAt = s('test')->bytesAt(0);
        if ($sBytesAt) {
            return true;
        }
        return false;
    }
}

?>
