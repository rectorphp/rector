<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use PHPStan\Type\MixedType;

class SkipPhpstanTrinary
{
    public function run(MixedType $variableType)
    {
        return $variableType->isSuperTypeOf($variableType)->yes();
    }
}
