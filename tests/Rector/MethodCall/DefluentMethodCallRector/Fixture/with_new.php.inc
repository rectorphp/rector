<?php

namespace Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

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

namespace Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

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
