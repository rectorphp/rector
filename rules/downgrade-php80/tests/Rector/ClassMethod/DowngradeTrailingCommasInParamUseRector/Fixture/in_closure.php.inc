<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosure
{
    public function run()
    {
        function (string $value1, string $value2,) {
        };
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosure
{
    public function run()
    {
        function (string $value1, string $value2) {
        };
    }
}

?>
