<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use PHPStan\Type\MixedType;

class SkipPhpStanTrinary
{
    public function run(MixedType $variableType)
    {
        return $variableType->isSuperTypeOf($variableType)->yes();
    }
}
