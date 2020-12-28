<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class NoParams
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

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class NoParams
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
