<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class ActionClass
{
    /**
     * @var FluentInterfaceClass
     */
    private $someClass;

    public function someFunction()
    {
        $this->someClass = new FluentInterfaceClass();

        $this->someClass->someFunction()
                        ->otherFunction();
    }
}

?>
-----
<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\FluentInterfaceClass;

class ActionClass
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
