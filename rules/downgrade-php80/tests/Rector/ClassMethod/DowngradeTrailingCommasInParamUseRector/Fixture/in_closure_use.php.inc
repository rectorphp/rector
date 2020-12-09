<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

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

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosureUse
{
    public function run($value1, $value2)
    {
        function () use ($value1, $value2) {
        };
    }
}

?>
