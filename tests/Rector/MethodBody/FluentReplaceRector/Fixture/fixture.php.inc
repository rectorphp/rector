<?php

namespace Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector;

use Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClass;

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

namespace Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector;

use Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClass;

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
