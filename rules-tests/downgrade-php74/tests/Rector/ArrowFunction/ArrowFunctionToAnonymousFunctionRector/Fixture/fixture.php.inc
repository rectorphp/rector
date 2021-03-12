<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $delimiter = ",";
        $callable = function ($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}

?>
