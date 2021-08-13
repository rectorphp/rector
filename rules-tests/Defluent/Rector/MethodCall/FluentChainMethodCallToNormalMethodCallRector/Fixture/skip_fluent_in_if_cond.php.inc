<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

final class SkipFluentInIfCond
{
    public function run()
    {
        $obj = new FluentInterfaceClass();

        if ($obj->someFunction()->otherFunction()) {

        }
    }
}
