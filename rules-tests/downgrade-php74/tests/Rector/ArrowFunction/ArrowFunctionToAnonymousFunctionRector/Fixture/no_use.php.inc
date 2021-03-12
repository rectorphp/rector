<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class NoUse
{
    public function duplicate()
    {
        $numbers = [3, 5, 7];
        return array_map(fn(int $number) => $number * 2, $numbers);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class NoUse
{
    public function duplicate()
    {
        $numbers = [3, 5, 7];
        return array_map(function (int $number) {
            return $number * 2;
        }, $numbers);
    }
}

?>
