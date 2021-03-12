<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosureUse
{
    public function run($value1, $value2)
    {
        function () use ($value1, $value2,) {
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosureUse
{
    public function run($value1, $value2)
    {
        function () use ($value1, $value2) {
        };
    }
}

?>
