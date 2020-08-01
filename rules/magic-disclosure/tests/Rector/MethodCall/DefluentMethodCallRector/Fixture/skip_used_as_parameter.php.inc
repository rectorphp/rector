<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

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
