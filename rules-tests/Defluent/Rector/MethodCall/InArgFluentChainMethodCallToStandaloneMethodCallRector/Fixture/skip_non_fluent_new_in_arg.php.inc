<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source\NonFluentClass;

class SkipNonFluentNewInArg
{
    public function someFunction()
    {
        $this->processFluentClass((new NonFluentClass())->number());
    }

    public function processFluentClass(int $number)
    {

    }
}
