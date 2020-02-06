<?php

namespace Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
    }
}

?>
