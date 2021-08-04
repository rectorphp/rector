<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipMethodCallVarAsVariableCall
{
    public function someFunction()
    {
        $someClass = new FluentInterfaceClass();

        return $someClass->someFunction()
                        ->otherFunction();
    }
}

?>
