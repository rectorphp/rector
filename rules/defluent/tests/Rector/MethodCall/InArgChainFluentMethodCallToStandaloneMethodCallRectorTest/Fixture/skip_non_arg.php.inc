<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

class SkipNonArg
{
    public function someFunction()
    {
        (new FluentClass())->otherFunction()
            ->someFunction();
    }
}
