<?php

namespace Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class DoNotRemoveReturnFromProperty
{
    private FluentInterfaceClass $property;

    public function __construct(FluentInterfaceClass $fluentInterfaceClass)
    {
        $this->property = $fluentInterfaceClass;
    }

    public function someFunction()
    {
        return $this->property->someFunction()->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class DoNotRemoveReturnFromProperty
{
    private FluentInterfaceClass $property;

    public function __construct(FluentInterfaceClass $fluentInterfaceClass)
    {
        $this->property = $fluentInterfaceClass;
    }

    public function someFunction()
    {
        $this->property->someFunction();
        return $this->property->otherFunction();
    }
}

?>
