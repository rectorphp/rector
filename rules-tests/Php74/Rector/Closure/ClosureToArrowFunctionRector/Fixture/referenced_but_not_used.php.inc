<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferencedButNotUsed
{
    public function run()
    {
        $callback = function($b) use(&$i) {
            return ++$b;
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferencedButNotUsed
{
    public function run()
    {
        $callback = fn($b) => ++$b;
    }
}

?>
