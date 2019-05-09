<?php

namespace Rector\Php\Tests\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class SkipReferencedValue
{
    public function run()
    {
        $callback = function() use(&$i) {
            return ++$i;
        };
    }
}
