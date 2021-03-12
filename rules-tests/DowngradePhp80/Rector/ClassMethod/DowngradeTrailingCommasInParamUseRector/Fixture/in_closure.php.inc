<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

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

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InClosure
{
    public function run()
    {
        function (string $value1, string $value2) {
        };
    }
}

?>
