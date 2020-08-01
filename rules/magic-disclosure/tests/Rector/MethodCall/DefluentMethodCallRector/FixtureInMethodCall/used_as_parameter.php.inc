<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\FixtureInMethodCall;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class UsedAsParameter
{
    public function someFunction(FluentInterfaceClass $someClass)
    {
        $this->processMoreFluentInterface($someClass->someFunction()->otherFunction());
    }

    public function processMoreFluentInterface(FluentInterfaceClass $someClass)
    {
    }
}

?>
-----
<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\FixtureInMethodCall;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class UsedAsParameter
{
    public function someFunction(FluentInterfaceClass $someClass)
    {
        $someClass->someFunction();
        $someClass->otherFunction();
        $this->processMoreFluentInterface($someClass);
    }

    public function processMoreFluentInterface(FluentInterfaceClass $someClass)
    {
    }
}

?>
