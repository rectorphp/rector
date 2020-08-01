<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction()->otherFunction());
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

?>
-----
<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $someClass->someFunction();
        $someClass->otherFunction();
        $this->processFluentClass($someClass);
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

?>
