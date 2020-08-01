<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

class SkipNonArg
{
    public function someFunction()
    {
        (new FluentClass())->otherFunction()
            ->someFunction();
    }
}
