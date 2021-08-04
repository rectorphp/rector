<?php

namespace Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class DoNotRemoveReturnFromParameter
{
    public function someFunction(FluentInterfaceClass $fluentInterfaceClass)
    {
        return $fluentInterfaceClass->someFunction()->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class DoNotRemoveReturnFromParameter
{
    public function someFunction(FluentInterfaceClass $fluentInterfaceClass)
    {
        $fluentInterfaceClass->someFunction();
        return $fluentInterfaceClass->otherFunction();
    }
}

?>
