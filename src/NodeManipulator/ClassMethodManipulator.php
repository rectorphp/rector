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
    public function isNamedConstructor(ClassMethod $classMethod): bool
    {
        if (!$this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        // visibility is local to the node; only the protected case needs the class final keyword
        if ($classMethod->isPrivate()) {
            return \true;
        }
        if (!$classMethod->isProtected()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return !$classReflection->isFinalByKeyword();
    }
    public function hasParentMethodOrInterfaceMethod(Class_ $class, string $methodName): bool
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
        $found = \false;
        foreach ($classReflection->getInterfaces() as $classReflection) {
            if ($classReflection->hasMethod($methodName)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
