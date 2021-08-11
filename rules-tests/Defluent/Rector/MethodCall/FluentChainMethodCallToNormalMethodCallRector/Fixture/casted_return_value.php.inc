<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class CastedReturnValue
{
    public function getCastedValue(DifferentReturnValues $differentReturnValues)
    {
        return (int) $differentReturnValues->someFunction()->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class CastedReturnValue
{
    public function getCastedValue(DifferentReturnValues $differentReturnValues)
    {
        $differentReturnValues->someFunction();
        return (int) $differentReturnValues->otherFunction();
    }
}

?>
