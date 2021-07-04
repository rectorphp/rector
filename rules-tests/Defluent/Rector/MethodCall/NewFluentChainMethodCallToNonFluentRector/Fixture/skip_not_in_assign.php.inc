<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;
use stdClass;

class SkipNotInAssign
{
    public function someFunction2()
    {
        $stdClass = new stdClass;
        (new FluentInterfaceClass())->someFunction2($stdClass)->otherFunction();
    }
}

?>
