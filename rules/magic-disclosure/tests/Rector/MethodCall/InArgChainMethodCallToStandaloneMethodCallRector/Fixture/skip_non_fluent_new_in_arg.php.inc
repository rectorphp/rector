<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Source\NonFluentClass;

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
