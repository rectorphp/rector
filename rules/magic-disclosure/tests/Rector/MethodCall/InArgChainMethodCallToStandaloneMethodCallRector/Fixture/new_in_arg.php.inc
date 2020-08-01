<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\Source\FluentClass;

class NewInArg
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass((new FluentClass())->someFunction());
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

class NewInArg
{
    public function someFunction(FluentClass $someClass)
    {
        $fluentClass = new FluentClass();
        $fluentClass->someFunction();
        $this->processFluentClass($fluentClass);
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

?>
