<?php

namespace Rector\Php74\Tests\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

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

namespace Rector\Php74\Tests\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class ReferencedButNotUsed
{
    public function run()
    {
        $callback = fn($b) => ++$b;
    }
}

?>
