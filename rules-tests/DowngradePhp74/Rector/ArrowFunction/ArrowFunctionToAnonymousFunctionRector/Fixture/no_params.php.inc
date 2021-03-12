<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

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

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

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
