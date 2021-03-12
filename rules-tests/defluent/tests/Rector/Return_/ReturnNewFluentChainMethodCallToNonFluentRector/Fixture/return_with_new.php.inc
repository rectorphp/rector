<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class ReturnWithNew
{
    public function someFunction()
    {
        return (new FluentInterfaceClass())->someFunction()->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class ReturnWithNew
{
    public function someFunction()
    {
        $fluentInterfaceClass = new FluentInterfaceClass();
        $fluentInterfaceClass->someFunction();
        $fluentInterfaceClass->otherFunction();
        return $fluentInterfaceClass;
    }
}

?>
