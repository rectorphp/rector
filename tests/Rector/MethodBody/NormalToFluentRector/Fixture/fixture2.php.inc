<?php

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass2
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction(1);
        $valueObject->otherFunction(2);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass2
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction(1)->otherFunction(2);
    }
}

?>
