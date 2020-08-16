<?php

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass3
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction();
        $valueObject->otherFunction();
        $valueObject->keepAsItIs();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass3
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
        $valueObject->keepAsItIs();
    }
}

?>
