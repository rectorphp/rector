<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass4
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction()->joinThisAsWell();
    }
}

?>
