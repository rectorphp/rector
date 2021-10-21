<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

use Iterator;

class NoUseParam
{
    public function duplicate()
    {
        $callable = fn () =>
            function (Iterator $iterator) {
                return $iterator;
            };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

use Iterator;

class NoUseParam
{
    public function duplicate()
    {
        $callable = function () {
            return function (Iterator $iterator) {
                return $iterator;
            };
        };
    }
}

?>
