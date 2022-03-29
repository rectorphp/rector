<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferencedInnerClass
{
    public function run()
    {
        $callback = function($b) use(&$i) {
            return new class { public function __construct($i)
                {
                }
            };
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferencedInnerClass
{
    public function run()
    {
        $callback = fn($b) => new class { public function __construct($i)
            {
            }
        };
    }
}

?>
