<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNullableReturnType
{
    public function run()
    {
        $callable = fn(): ?string => 'Hello world';
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNullableReturnType
{
    public function run()
    {
        $callable = function () : ?string {
            return 'Hello world';
        };
    }
}

?>
