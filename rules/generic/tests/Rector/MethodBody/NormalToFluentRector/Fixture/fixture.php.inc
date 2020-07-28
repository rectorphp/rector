<?php

namespace Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
    }
}

?>
