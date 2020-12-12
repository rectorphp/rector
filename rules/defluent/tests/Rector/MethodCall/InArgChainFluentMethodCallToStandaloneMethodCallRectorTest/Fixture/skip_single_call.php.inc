<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

class SkipSingleClass
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction());
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}
