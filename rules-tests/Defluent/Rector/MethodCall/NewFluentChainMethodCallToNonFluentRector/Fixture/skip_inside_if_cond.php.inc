<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class SkipInsideIfCond
{
    public function someFunction()
    {
        if ($instance = (new FluentInterfaceClass())->someFunction()->otherFunction()) {
            return $instance;
        }
    }
}

?>