<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class UsedAsParameter
{
    public function someFunction(FluentInterfaceClass $someClass)
    {
        $this->processMoreFluentInterface($someClass->someFunction()->otherFunction());
    }

    public function processMoreFluentInterface(FluentInterfaceClass $someClass)
    {
    }
}
