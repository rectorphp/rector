<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

function helper(): FluentInterfaceClass
{
    return new FluentInterfaceClass();
}

class SkipMethodCallVarAsFuncCall
{
    public function someFunction()
    {
        return helper()->someFunction()->otherFunction();
    }
}

?>
