<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipInsideTernaryElse2Casted
{
    public function someFunction()
    {
        $obj = new FluentInterfaceClass();

        return rand(0, 1)
            ? 1
            : (int) $obj->someFunction()->otherFunction();
    }
}

?>
