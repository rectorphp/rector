<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
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
        /** @var ParametersAcceptorWithPhpDocs[] $parametersAcceptors */
        $parametersAcceptors = $type->getCallableParametersAcceptors($scope);
        return new NativeFunctionReflection('{closure}', $parametersAcceptors, null, TrinaryLogic::createMaybe(), \false);
    }
}
