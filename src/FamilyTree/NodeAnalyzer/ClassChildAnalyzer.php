<?php

declare (strict_types=1);
namespace Rector\FamilyTree\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\PhpParser\AstResolver;
final class ClassChildAnalyzer
{
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    public function __construct(AstResolver $astResolver)
    {
        $this->astResolver = $astResolver;
    }
    /**
     * Look both parent class and interface, yes, all PHP interface methods are abstract
     */
    public function hasAbstractParentClassMethod(ClassReflection $classReflection, string $methodName) : bool
    {
        $parentClassMethods = $this->resolveParentClassMethods($classReflection, $methodName);
        if ($parentClassMethods === []) {
            return \false;
        }
        foreach ($parentClassMethods as $parentClassMethod) {
            if ($parentClassMethod->isAbstract()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @api downgrade
     */
    public function resolveParentClassMethodReturnType(ClassReflection $classReflection, string $methodName) : Type
    {
        $parentClassMethods = $this->resolveParentClassMethods($classReflection, $methodName);
        if ($parentClassMethods === []) {
            return new MixedType();
        }
        foreach ($parentClassMethods as $parentClassMethod) {
            // for downgrade purpose on __toString
            // @see https://3v4l.org/kdcEh#v7.4.33
            // @see https://github.com/phpstan/phpstan-src/commit/3854cbc5748a7cb51ee0b86ceffe29bd0564bc98
            if ($parentClassMethod->getDeclaringClass()->isBuiltIn() || $methodName !== '__toString') {
                $nativeReturnType = $this->resolveNativeType($parentClassMethod);
            } else {
                $nativeReturnType = $this->resolveToStringNativeTypeFromAstResolver($parentClassMethod);
            }
            if (!$nativeReturnType instanceof MixedType) {
                return $nativeReturnType;
            }
        }
        return new MixedType();
    }
    private function resolveNativeType(PhpMethodReflection $phpMethodReflection) : Type
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($phpMethodReflection->getVariants());
        return $extendedParametersAcceptor->getNativeReturnType();
    }
    private function resolveToStringNativeTypeFromAstResolver(PhpMethodReflection $phpMethodReflection) : Type
    {
        $classReflection = $phpMethodReflection->getDeclaringClass();
        $class = $this->astResolver->resolveClassFromClassReflection($classReflection);
        if ($class instanceof ClassLike) {
            $classMethod = $class->getMethod($phpMethodReflection->getName());
            if ($classMethod instanceof ClassMethod && !$classMethod->returnType instanceof Node) {
                return new MixedType();
            }
        }
        return new StringType();
    }
    /**
     * @return PhpMethodReflection[]
     */
    private function resolveParentClassMethods(ClassReflection $classReflection, string $methodName) : array
    {
        if ($classReflection->hasNativeMethod($methodName) && $classReflection->getNativeMethod($methodName)->isPrivate()) {
            return [];
        }
        $parentClassMethods = [];
        $parents = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($parents as $parent) {
            if (!$parent->hasNativeMethod($methodName)) {
                continue;
            }
            $methodReflection = $parent->getNativeMethod($methodName);
            if (!$methodReflection instanceof PhpMethodReflection) {
                continue;
            }
            $methodDeclaringMethodClass = $methodReflection->getDeclaringClass();
            if ($methodDeclaringMethodClass->getName() === $parent->getName()) {
                $parentClassMethods[] = $methodReflection;
            }
        }
        return $parentClassMethods;
    }
}
