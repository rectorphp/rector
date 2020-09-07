<?php

namespace Rector\Downgrade\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNoParamsClass
{
    public function run()
    {
        $delimiter = " ";
        $callable = fn() => 'Hello' . $delimiter . 'world';
    }
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class WithNoParamsClass
{
    public function run()
    {
        $delimiter = " ";
        $callable = function () use ($delimiter) {
            return 'Hello' . $delimiter . 'world';
        };
    }
}

?>
