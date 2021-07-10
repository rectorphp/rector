<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipLastCallNotReturnCurrentObject
{
    /**
     * @var FluentInterfaceClass
     */
    private $someClass;

    public function someFunction()
    {
        $this->someClass = new FluentInterfaceClass();

        return $this->someClass->someFunction()
                        ->otherFunction()
                        ->paginate();
    }

    public function someFunction2()
    {
        $this->someClass = new FluentInterfaceClass();

        return $this->someClass->someFunction()
                        ->otherFunction()
                        ->voidReturningMethod();
    }
}

?>
