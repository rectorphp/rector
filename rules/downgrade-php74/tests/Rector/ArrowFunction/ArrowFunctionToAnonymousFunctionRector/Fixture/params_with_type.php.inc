<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class ParamsWithType
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn(array $matches) => $delimiter . strtolower($matches[1]);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class ParamsWithType
{
    public function run()
    {
        $delimiter = ",";
        $callable = function (array $matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}

?>
