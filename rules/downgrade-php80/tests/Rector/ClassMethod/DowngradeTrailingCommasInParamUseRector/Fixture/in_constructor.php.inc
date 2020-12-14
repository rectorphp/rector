<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InConstructor
{
    public function getObject(): object
    {
        return new ConfiguredCodeSample('foo',);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InConstructor
{
    public function getObject(): object
    {
        return new ConfiguredCodeSample('foo');
    }
}

?>
