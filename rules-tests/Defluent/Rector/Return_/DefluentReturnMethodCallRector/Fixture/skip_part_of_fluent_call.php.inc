<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipPartOfFluentCall
{
    public function someFunction()
    {
        $someClass = new FluentInterfaceClass();
        return $someClass->someFunction()
            ->anotherFunction();
    }
}
