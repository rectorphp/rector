<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class LastNormalCallOnFluent
{
    public function someMethod(FluentInterfaceClass $fluentInterfaceClass)
    {
        $fluentInterfaceClass->someFunction()
            ->voidReturningMethod();
    }
}

?>
-----
<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class LastNormalCallOnFluent
{
    public function someMethod(FluentInterfaceClass $fluentInterfaceClass)
    {
        $fluentInterfaceClass->someFunction();
        $fluentInterfaceClass->voidReturningMethod();
    }
}

?>
