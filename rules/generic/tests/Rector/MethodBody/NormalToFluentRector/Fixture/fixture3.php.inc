<?php

namespace Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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
