<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

final class StaticFn
{
    public function run()
    {
        $callable = static fn () => true;
    }
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

final class StaticFn
{
    public function run()
    {
        $callable = static function () {
            return true;
        };
    }
}
?>
