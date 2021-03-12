<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InFuncCall
{
    public function run(): void
    {
        var_dump('foo',);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InFuncCall
{
    public function run(): void
    {
        var_dump('foo');
    }
}

?>
