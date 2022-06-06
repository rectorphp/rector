<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
/**
 * @implements TypeToCallReflectionResolverInterface<ClosureType>
 */
final class ClosureTypeToCallReflectionResolver implements \Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface
{
    public function supports(\PHPStan\Type\Type $type) : bool
    {
        return $type instanceof \PHPStan\Type\ClosureType;
    }
    /**
     * @param ClosureType $type
     */
    public function resolve(\PHPStan\Type\Type $type, \PHPStan\Analyser\Scope $scope) : \PHPStan\Reflection\Native\NativeFunctionReflection
    {
        return new \PHPStan\Reflection\Native\NativeFunctionReflection('{closure}', $type->getCallableParametersAcceptors($scope), null, \PHPStan\TrinaryLogic::createMaybe(), \false);
    }
}
