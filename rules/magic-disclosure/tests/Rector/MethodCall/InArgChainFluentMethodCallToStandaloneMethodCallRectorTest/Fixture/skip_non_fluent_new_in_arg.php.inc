<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\NonFluentClass;

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
