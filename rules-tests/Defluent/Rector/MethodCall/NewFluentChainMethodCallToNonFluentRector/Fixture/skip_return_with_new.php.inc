<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class SkipReturnWithNew
{
    public function someFunction()
    {
        return (new FluentInterfaceClass())->someFunction()->otherFunction();
    }
}
