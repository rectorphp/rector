<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

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
