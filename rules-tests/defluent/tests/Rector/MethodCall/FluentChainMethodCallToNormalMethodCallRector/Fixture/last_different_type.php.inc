<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class KeepLastDifferentType
{
    public function run()
    {
        $differentReturnValues = new DifferentReturnValues();
        $differentReturnValues->someFunction()
            ->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class KeepLastDifferentType
{
    public function run()
    {
        $differentReturnValues = new DifferentReturnValues();
        $differentReturnValues->someFunction();
        $differentReturnValues->otherFunction();
    }
}

?>
