<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector\Source\FluentInterfaceClass;

class SkipNoReturnWithNew
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
