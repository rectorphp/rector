<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\NonFluentClass;

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
