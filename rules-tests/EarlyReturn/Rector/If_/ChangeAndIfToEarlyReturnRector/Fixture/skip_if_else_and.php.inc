<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

use PHPStan\Type\CallableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class SkipIfElseAnd
{
    public function isClosureAndCallableType(Type $parameterStaticType, Type $argumentStaticType): bool
    {
        if ($parameterStaticType instanceof CallableType && $this->isClosureObjectType($argumentStaticType)) {
            return true;
        }

        return $argumentStaticType instanceof CallableType && $this->isClosureObjectType($parameterStaticType);
    }

    private function isClosureObjectType(Type $type): bool
    {
        if (! $type instanceof ObjectType) {
            return false;
        }

        return $type->getClassName() === 'Closure';
    }
}
