<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
final class ClosureTypeToCallReflectionResolver implements \Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface
{
    /**
     * @param \PHPStan\Type\Type $type
     */
    public function supports($type) : bool
    {
        return $type instanceof \PHPStan\Type\ClosureType;
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \PHPStan\Analyser\Scope $scope
     */
    public function resolve($type, $scope) : \PHPStan\Reflection\Native\NativeFunctionReflection
    {
        return new \PHPStan\Reflection\Native\NativeFunctionReflection('{closure}', $type->getCallableParametersAcceptors($scope), null, \PHPStan\TrinaryLogic::createMaybe());
    }
}
