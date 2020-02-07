<?php

namespace Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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
