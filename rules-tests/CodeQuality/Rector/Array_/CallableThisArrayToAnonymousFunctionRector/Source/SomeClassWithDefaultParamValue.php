<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source;

class SomeClassWithDefaultParamValue
{
    public function run($a, $b = ['test'])
    {
        return $a . $b;
    }
}
