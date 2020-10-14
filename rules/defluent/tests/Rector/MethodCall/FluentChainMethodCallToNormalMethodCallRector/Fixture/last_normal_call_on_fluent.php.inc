<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

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

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class LastNormalCallOnFluent
{
    public function someMethod(FluentInterfaceClass $fluentInterfaceClass)
    {
        $fluentInterfaceClass->someFunction();
        $fluentInterfaceClass->voidReturningMethod();
    }
}

?>
