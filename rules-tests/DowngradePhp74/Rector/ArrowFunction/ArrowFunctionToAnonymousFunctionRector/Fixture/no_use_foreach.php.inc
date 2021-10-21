<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

use Iterator;

class NoUseForeach
{
    public function duplicate()
    {
        $callable = fn () =>
            function (Iterator $iterator) {
                foreach ($iterator as $foo => $bar) {
                    yield $foo => $bar;
                }
            };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

use Iterator;

class NoUseForeach
{
    public function duplicate()
    {
        $callable = function () {
            return function (Iterator $iterator) {
                foreach ($iterator as $foo => $bar) {
                    yield $foo => $bar;
                }
            };
        };
    }
}

?>
