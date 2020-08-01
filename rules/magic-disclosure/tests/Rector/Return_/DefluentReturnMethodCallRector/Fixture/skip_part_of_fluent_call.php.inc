<?php

namespace Rector\MagicDisclosure\Tests\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipPartOfFluentCall
{
    public function someFunction()
    {
        $someClass = new FluentInterfaceClass();
        return $someClass->someFunction()
            ->anotherFunction();
    }
}
