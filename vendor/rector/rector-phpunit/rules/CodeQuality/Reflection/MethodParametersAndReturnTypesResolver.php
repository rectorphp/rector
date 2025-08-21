<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Enum\ClassName;
use Rector\PHPUnit\CodeQuality\ValueObject\ParamTypesAndReturnType;
final class MethodParametersAndReturnTypesResolver
{
    public function resolveFromReflection(IntersectionType $intersectionType, string $methodName, ClassReflection $currentClassReflection) : ?ParamTypesAndReturnType
    {
        foreach ($intersectionType->getTypes() as $intersectionedType) {
            if (!$intersectionedType instanceof ObjectType) {
                continue;
            }
            if ($intersectionedType->getClassName() === ClassName::MOCK_OBJECT) {
                continue;
            }
            $classReflection = $intersectionedType->getClassReflection();
            if (!$classReflection instanceof ClassReflection) {
                continue;
            }
            if (!$classReflection->hasNativeMethod($methodName)) {
                continue;
            }
            $mockedMethodReflection = $classReflection->getNativeMethod($methodName);
            $parameterTypes = $this->resolveParameterTypes($mockedMethodReflection, $currentClassReflection);
            $returnType = $this->resolveReturnType($mockedMethodReflection, $currentClassReflection);
            return new ParamTypesAndReturnType($parameterTypes, $returnType);
        }
        return null;
    }
    /**
     * @return Type[]
     */
    private function resolveParameterTypes(ExtendedMethodReflection $extendedMethodReflection, ClassReflection $currentClassReflection) : array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $parameterTypes = [];
        foreach ($extendedParametersAcceptor->getParameters() as $parameterReflection) {
            $parameterType = $parameterReflection->getNativeType();
            if ($parameterType->isObject()->yes() && $currentClassReflection->getName() !== $parameterType->getClassReflection()->getName()) {
                return [];
            }
            $parameterTypes[] = $parameterType;
        }
        return $parameterTypes;
    }
    private function resolveReturnType(ExtendedMethodReflection $extendedMethodReflection, ClassReflection $currentClassReflection) : Type
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $returnType = $extendedParametersAcceptor->getNativeReturnType();
        if ($returnType->isObject()->yes() && $currentClassReflection->getName() !== $returnType->getClassReflection()->getName()) {
            return new MixedType();
        }
        return $returnType;
    }
}
