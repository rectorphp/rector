<?php

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass4
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction();
        $valueObject->otherFunction();
        $valueObject->joinThisAsWell();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass4
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction()->joinThisAsWell();
    }
}

?>
