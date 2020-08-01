<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

class SkipNonArg
{
    public function someFunction()
    {
        (new FluentClass())->otherFunction()
            ->someFunction();
    }
}
