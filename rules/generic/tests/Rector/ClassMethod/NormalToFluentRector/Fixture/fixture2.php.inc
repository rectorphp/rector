<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

class Fixture2
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

namespace Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;

class Fixture2
{
    public function someFunction()
    {
        $valueObject = new FluentInterfaceClass();
        $valueObject->someFunction(1)->otherFunction(2);
    }
}

?>
