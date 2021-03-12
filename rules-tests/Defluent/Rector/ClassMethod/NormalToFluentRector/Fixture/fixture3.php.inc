<?php

namespace Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

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

namespace Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

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
