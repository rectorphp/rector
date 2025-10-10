<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Reflection;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
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
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(NodeTypeResolver $nodeTypeResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $callLike
     */
    public function resolveCallParameterTypes($callLike): ?array
    {
        if ($callLike instanceof New_) {
            if (!$callLike->class instanceof Name) {
                return null;
            }
            $className = $callLike->class->toString();
            if (!$this->reflectionProvider->hasClass($className)) {
                return null;
            }
            $classReflection = $this->reflectionProvider->getClass($className);
            if (!$classReflection->hasConstructor()) {
                return null;
            }
            return $this->resolveParameterTypes($classReflection->getConstructor(), $classReflection);
        }
        if (!$callLike->name instanceof Identifier) {
            return null;
        }
        $methodName = $callLike->name->toString();
        $callerType = $this->nodeTypeResolver->getType($callLike instanceof MethodCall ? $callLike->var : $callLike->class);
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $callLike
     */
    public function resolveCallParameterNames($callLike): array
    {
        if ($callLike instanceof New_) {
            if (!$callLike->class instanceof Name) {
                return [];
            }
            $className = $callLike->class->toString();
            if (!$this->reflectionProvider->hasClass($className)) {
                return [];
            }
            $classReflection = $this->reflectionProvider->getClass($className);
            if (!$classReflection->hasConstructor()) {
                return [];
            }
            return $this->resolveParameterNames($classReflection->getConstructor());
        }
        if (!$callLike->name instanceof Identifier) {
            return [];
        }
        $methodName = $callLike->name->toString();
        $callerType = $this->nodeTypeResolver->getType($callLike instanceof MethodCall ? $callLike->var : $callLike->class);
        if ($callerType instanceof ThisType) {
            $callerType = $callerType->getStaticObjectType();
        }
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
            if ($parameterType instanceof ObjectType && $currentClassReflection->getName() !== (($nullsafeVariable1 = $parameterType->getClassReflection()) ? $nullsafeVariable1->getName() : null)) {
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
        if ($returnType instanceof ObjectType && $currentClassReflection->getName() !== (($nullsafeVariable2 = $returnType->getClassReflection()) ? $nullsafeVariable2->getName() : null)) {
            return new MixedType();
        }
        return $returnType;
    }
}
