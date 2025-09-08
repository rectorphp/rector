<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Reflection;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Enum\ClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPUnit\CodeQuality\ValueObject\ParamTypesAndReturnType;
final class MethodParametersAndReturnTypesResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveFromReflection(IntersectionType $intersectionType, string $methodName, ClassReflection $currentClassReflection): ?ParamTypesAndReturnType
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
     * @return null|Type[]
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function resolveCallParameterTypes($call): ?array
    {
        if (!$call->name instanceof Identifier) {
            return null;
        }
        $methodName = $call->name->toString();
        $callerType = $this->nodeTypeResolver->getType($call instanceof MethodCall ? $call->var : $call->class);
        if ($callerType instanceof ThisType) {
            $callerType = $callerType->getStaticObjectType();
        }
        if (!$callerType instanceof ObjectType) {
            return null;
        }
        $classReflection = $callerType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->hasNativeMethod($methodName)) {
            return null;
        }
        $extendedMethodReflection = $classReflection->getNativeMethod($methodName);
        return $this->resolveParameterTypes($extendedMethodReflection, $classReflection);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function resolveCallParameterNames($call): array
    {
        if (!$call->name instanceof Identifier) {
            return [];
        }
        $methodName = $call->name->toString();
        $callerType = $this->nodeTypeResolver->getType($call instanceof MethodCall ? $call->var : $call->class);
        if (!$callerType instanceof ObjectType) {
            return [];
        }
        $classReflection = $callerType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        if (!$classReflection->hasNativeMethod($methodName)) {
            return [];
        }
        $extendedMethodReflection = $classReflection->getNativeMethod($methodName);
        return $this->resolveParameterNames($extendedMethodReflection);
    }
    /**
     * @return Type[]
     */
    public function resolveParameterTypes(ExtendedMethodReflection $extendedMethodReflection, ClassReflection $currentClassReflection): array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $parameterTypes = [];
        foreach ($extendedParametersAcceptor->getParameters() as $parameterReflection) {
            $parameterType = $this->resolveObjectType($parameterReflection->getNativeType());
            if ($parameterType instanceof ObjectType && $currentClassReflection->getName() !== $parameterType->getClassReflection()->getName()) {
                $parameterTypes[] = new MixedType();
                continue;
            }
            $parameterTypes[] = $parameterType;
        }
        return $parameterTypes;
    }
    /**
     * @return string[]
     */
    private function resolveParameterNames(ExtendedMethodReflection $extendedMethodReflection): array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $parameterNames = [];
        foreach ($extendedParametersAcceptor->getParameters() as $parameterReflection) {
            $parameterNames[] = $parameterReflection->getName();
        }
        return $parameterNames;
    }
    /**
     * @return \PHPStan\Type\ObjectType|\PHPStan\Type\Type
     */
    private function resolveObjectType(Type $type)
    {
        if ($type instanceof ObjectType) {
            return $type;
        }
        if ($type instanceof StaticType) {
            return $type->getStaticObjectType();
        }
        return $type;
    }
    private function resolveReturnType(ExtendedMethodReflection $extendedMethodReflection, ClassReflection $currentClassReflection): Type
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $returnType = $this->resolveObjectType($extendedParametersAcceptor->getNativeReturnType());
        if ($returnType instanceof ObjectType && $currentClassReflection->getName() !== $returnType->getClassReflection()->getName()) {
            return new MixedType();
        }
        return $returnType;
    }
}
