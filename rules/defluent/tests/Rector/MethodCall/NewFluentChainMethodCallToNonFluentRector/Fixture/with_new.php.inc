<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class WithNew
{
    /**
     * @var FluentInterfaceClass
     */
    private $someClass;

    public function someFunction()
    {
        $this->someClass = (new FluentInterfaceClass())->someFunction()->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class WithNew
{
    /**
     * @var FluentInterfaceClass
     */
    private $someClass;

    public function someFunction()
    {
        $this->someClass = new FluentInterfaceClass();
        $this->someClass->someFunction();
        $this->someClass->otherFunction();
    }
}

?>
