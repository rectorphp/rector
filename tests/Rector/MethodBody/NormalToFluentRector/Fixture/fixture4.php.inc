<?php

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass4
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction()->joinThisAsWell();
    }
}

?>
