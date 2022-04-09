<?php

namespace Rector\Tests\Php74\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class SkipReferenceToInnerClosure
{
    public function run($a)
    {
        $y = function() use (&$a) {
            return function() use (&$a){
                $a[] = 'a';
            };
        };

        return $y;
    }
}
