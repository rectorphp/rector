<?php

namespace Rector\MagicDisclosure\Tests\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class SomeClass
{
    public function someFunction()
    {
        $someClass = new FluentInterfaceClass();
        return $someClass->someFunction();
    }
}

?>
-----
<?php

namespace Rector\MagicDisclosure\Tests\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class SomeClass
{
    public function someFunction()
    {
        $someClass = new FluentInterfaceClass();
        $someClass->someFunction();
        return $someClass;
    }
}

?>
