<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

class SkipUseReturn
{
    public function run()
    {
        return $this->processFluentClass((new FluentClass())->someFunction());
    }

    public function processFluentClass($value)
    {
        return $this;
    }
}

?>