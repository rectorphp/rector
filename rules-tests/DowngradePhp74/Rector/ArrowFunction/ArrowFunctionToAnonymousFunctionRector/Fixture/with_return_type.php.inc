<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithReturnType
{
    public function run()
    {
        $callable = fn(): string => 'Hello world';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithReturnType
{
    public function run()
    {
        $callable = function () : string {
            return 'Hello world';
        };
    }
}

?>
