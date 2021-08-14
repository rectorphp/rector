<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

class SkipCasted
{
    public function run()
    {
        $data = (int) $this->processFluentClass((new FluentClass())->someFunction()->otherFunction2());
    }

    public function processFluentClass($value)
    {
        return $value;
    }
}

?>
