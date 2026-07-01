<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Reflection\ReflectionResolver;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
final class ClassMethodParamVendorLockResolver
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function isVendorLocked(ClassMethod $classMethod): bool
    {
        if ($classMethod->isMagic()) {
            return \true;
        }
        // user-guarded class: adding a param type here would break its child classes
        if ($this->parentClassMethodTypeOverrideGuard->isTypeGuardedClass($classMethod)) {
            return \true;
        }
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        // has interface vendor lock? → better skip it, as PHPStan has access only to just analyzed classes
        return $this->hasParentInterfaceMethod($classReflection, $methodName);
    }
    /**
     * Has interface even in our project?
     * Better skip it, as PHPStan has access only to just analyzed classes.
     * This might change type, that works for current class, but breaks another implementer.
     */
    private function hasParentInterfaceMethod(ClassReflection $classReflection, string $methodName): bool
    {
        $found = \false;
        foreach ($classReflection->getInterfaces() as $interfaceClassReflection) {
            if ($interfaceClassReflection->hasMethod($methodName)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
