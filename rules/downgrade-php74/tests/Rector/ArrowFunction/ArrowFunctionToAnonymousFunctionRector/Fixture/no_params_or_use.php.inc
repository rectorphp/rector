<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNoParamsOrUseClass
{
    public function run()
    {
        $callable = fn() => 'Hello world';
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNoParamsOrUseClass
{
    public function run()
    {
        $callable = function () {
            return 'Hello world';
        };
    }
}

?>
