<?php

namespace Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
    }
}

?>
