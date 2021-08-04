<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\FluentInterfaceClass;

class SkipMethodCallVarAsStaticCall
{
    private static $someClass;

    public function someFunction()
    {
        self::$someClass = new FluentInterfaceClass();

        return self::$someClass->someFunction()
                        ->otherFunction();
    }
}

?>
