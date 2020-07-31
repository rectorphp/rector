<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

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

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class LastNormalCallOnFluent
{
    public function someMethod(FluentInterfaceClass $fluentInterfaceClass)
    {
        $fluentInterfaceClass->someFunction();
        $fluentInterfaceClass->voidReturningMethod();
    }
}

?>
