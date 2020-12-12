<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNullableReturnTypeClass
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

class WithNullableReturnTypeClass
{
    public function run()
    {
        $callable = function () : ?string {
            return 'Hello world';
        };
    }
}

?>
