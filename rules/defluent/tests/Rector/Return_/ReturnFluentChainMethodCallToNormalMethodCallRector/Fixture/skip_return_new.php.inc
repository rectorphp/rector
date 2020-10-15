<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipReturnNew
{
    public function someFunction()
    {
        return (new FluentInterfaceClass())->someFunction()->otherFunction();
    }
}
