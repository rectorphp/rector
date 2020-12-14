<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InMethodCall
{
    public function run(): void
    {
        var_dump('foo',);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InMethodCall
{
    public function run(): void
    {
        var_dump('foo');
    }
}

?>
