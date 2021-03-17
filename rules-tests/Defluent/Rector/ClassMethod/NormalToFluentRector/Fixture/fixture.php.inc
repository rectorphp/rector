<?php

namespace Rector\Tests\Defluent\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Tests\Defluent\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction()->otherFunction();
    }
}

?>
