<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

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

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

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
