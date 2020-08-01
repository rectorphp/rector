<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

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
