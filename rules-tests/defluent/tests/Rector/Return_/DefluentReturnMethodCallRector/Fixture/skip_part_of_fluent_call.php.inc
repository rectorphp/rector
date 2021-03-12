<?php

namespace Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipPartOfFluentCall
{
    public function someFunction()
    {
        $someClass = new FluentInterfaceClass();
        return $someClass->someFunction()
            ->anotherFunction();
    }
}
