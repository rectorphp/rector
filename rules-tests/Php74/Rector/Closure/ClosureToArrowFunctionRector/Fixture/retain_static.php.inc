<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class RetainStatic
{
    public function run()
    {
        $bars = array_map(static function (int $value): int {
            return $value * 2;
        }, [1, 2, 3]);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class RetainStatic
{
    public function run()
    {
        $bars = array_map(static fn(int $value): int => $value * 2, [1, 2, 3]);
    }
}

?>
