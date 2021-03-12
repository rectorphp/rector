<?php

namespace Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

class ActionClass2
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction(1)->otherFunction(2);
    }
}

?>
