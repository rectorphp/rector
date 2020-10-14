<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class SkipInArgWithNew
{
    /**
     * @var FluentInterfaceClass
     */
    private $someClass;

    public function someFunction()
    {
        return strlen((new FluentInterfaceClass())->someFunction()->otherFunction());
    }
}
