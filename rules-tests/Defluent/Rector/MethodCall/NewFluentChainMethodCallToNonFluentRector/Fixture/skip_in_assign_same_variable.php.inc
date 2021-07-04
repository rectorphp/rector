<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;
use stdClass;

class SkipInAssignSameVariable
{
    public function someFunction2()
    {
        $obj = new stdClass;
        $obj = (new FluentInterfaceClass())->someFunction2($obj)->otherFunction();
    }
}

?>
