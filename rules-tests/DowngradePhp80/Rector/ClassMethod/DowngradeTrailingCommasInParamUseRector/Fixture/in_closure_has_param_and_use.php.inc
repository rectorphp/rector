<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosureHasParamAndUse
{
    public function run($param1, $param2, $value1, $value2)
    {
        function ($param1, $param2,) use ($value1, $value2,) {
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosureHasParamAndUse
{
    public function run($param1, $param2, $value1, $value2)
    {
        function ($param1, $param2) use ($value1, $value2) {
        };
    }
}

?>
