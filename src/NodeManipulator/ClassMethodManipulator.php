<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
final class ClassMethodManipulator
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isNamedConstructor(ClassMethod $classMethod) : bool
    {
        if (!$this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classMethod->isPrivate()) {
            return \true;
        }
        if ($classReflection->isFinalByKeyword()) {
            return \false;
        }
        return $classMethod->isProtected();
    }
    public function hasParentMethodOrInterfaceMethod(Class_ $class, string $methodName) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
            if ($parentClassReflection->hasMethod(MethodName::CALL)) {
                return \true;
            }
        }
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
}
