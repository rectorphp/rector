<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

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

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

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
