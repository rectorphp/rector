<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\NonFluentClass;

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
