<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

class WithArgs
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction(100)->otherFunction([1, 2, 3]));
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\FluentClass;

class WithArgs
{
    public function someFunction(FluentClass $someClass)
    {
        $someClass->someFunction(100);
        $someClass->otherFunction([1, 2, 3]);
        $this->processFluentClass($someClass);
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

?>
