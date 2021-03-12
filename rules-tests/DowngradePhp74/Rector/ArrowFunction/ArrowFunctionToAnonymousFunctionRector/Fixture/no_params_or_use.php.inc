<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class NoParamsOrUse
{
    public function run()
    {
        $callable = fn() => 'Hello world';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class NoParamsOrUse
{
    public function run()
    {
        $callable = function () {
            return 'Hello world';
        };
    }
}

?>
