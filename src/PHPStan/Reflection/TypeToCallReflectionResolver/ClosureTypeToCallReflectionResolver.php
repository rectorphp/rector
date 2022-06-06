<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\Native\NativeFunctionReflection;
use RectorPrefix20220606\PHPStan\TrinaryLogic;
use RectorPrefix20220606\PHPStan\Type\ClosureType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
/**
 * @implements TypeToCallReflectionResolverInterface<ClosureType>
 */
final class ClosureTypeToCallReflectionResolver implements TypeToCallReflectionResolverInterface
{
    public function supports(Type $type) : bool
    {
        return $type instanceof ClosureType;
    }
    /**
     * @param ClosureType $type
     */
    public function resolve(Type $type, Scope $scope) : NativeFunctionReflection
    {
        return new NativeFunctionReflection('{closure}', $type->getCallableParametersAcceptors($scope), null, TrinaryLogic::createMaybe(), \false);
    }
}
