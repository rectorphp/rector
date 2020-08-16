<?php

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction();
        $valueObject->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
    }
}

?>
