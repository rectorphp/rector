<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferenceToInnerClosureUnused
{
    public function run($a)
    {
        $y = function() use (&$a) {
            return function() {
                return 1;
            };
        };

        return $y;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferenceToInnerClosureUnused
{
    public function run($a)
    {
        $y = fn() => fn() => 1;

        return $y;
    }
}

?>
