<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipNew
{
    public function someFunction()
    {
        return (new FluentInterfaceClass())->someFunction()->otherFunction();
    }
}
