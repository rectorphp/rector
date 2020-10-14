<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class ReturnBasicDoubleFluent
{
    /**
     * @var FluentInterfaceClass
     */
    private $someClass;

    public function someFunction()
    {
        $this->someClass = new FluentInterfaceClass();

        return $this->someClass->someFunction()
                        ->otherFunction();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class ReturnBasicDoubleFluent
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
        return $this->someClass;
    }
}

?>
