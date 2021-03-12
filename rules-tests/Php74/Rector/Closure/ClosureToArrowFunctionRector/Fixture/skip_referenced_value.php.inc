<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class SkipReferencedValue
{
    public function run()
    {
        $callback = function() use(&$i) {
            return ++$i;
        };
    }
}
