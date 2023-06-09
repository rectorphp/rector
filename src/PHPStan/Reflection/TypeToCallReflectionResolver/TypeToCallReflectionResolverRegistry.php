<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
final class TypeToCallReflectionResolverRegistry
{
    /**
     * @var TypeToCallReflectionResolverInterface[]
     */
    private $typeToCallReflectionResolvers = [];
    public function __construct(\Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\ClosureTypeToCallReflectionResolver $closureTypeToCallReflectionResolver, \Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\ConstantArrayTypeToCallReflectionResolver $constantArrayTypeToCallReflectionResolver, \Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\ConstantStringTypeToCallReflectionResolver $constantStringTypeToCallReflectionResolver, \Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver\ObjectTypeToCallReflectionResolver $objectTypeToCallReflectionResolver)
    {
        $this->typeToCallReflectionResolvers = [$closureTypeToCallReflectionResolver, $constantArrayTypeToCallReflectionResolver, $constantStringTypeToCallReflectionResolver, $objectTypeToCallReflectionResolver];
    }
    /**
     * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
     */
    public function resolve(Type $type, Scope $scope)
    {
        foreach ($this->typeToCallReflectionResolvers as $typeToCallReflectionResolver) {
            if (!$typeToCallReflectionResolver->supports($type)) {
                continue;
            }
            return $typeToCallReflectionResolver->resolve($type, $scope);
        }
        return null;
    }
}
