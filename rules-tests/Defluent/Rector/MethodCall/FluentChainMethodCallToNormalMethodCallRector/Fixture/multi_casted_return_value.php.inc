<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class MultiCastedReturnValue
{
    public function getCastedValue(DifferentReturnValues $differentReturnValues)
    {
        return (int) (int) (int) $differentReturnValues->someFunction()->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class MultiCastedReturnValue
{
    public function getCastedValue(DifferentReturnValues $differentReturnValues)
    {
        $differentReturnValues->someFunction();
        return (int) $differentReturnValues->otherFunction();
    }
}

?>
