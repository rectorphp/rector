<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Type\MixedType;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassMethodReturnVendorLockResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isVendorLocked(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->isVendorLockedByAncestors($classReflection, $methodName);
    }
    private function isVendorLockedByAncestors(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        foreach ($classReflection->getAncestors() as $ancestorClassReflections) {
            if ($ancestorClassReflections === $classReflection) {
                continue;
            }
            $nativeClassReflection = $ancestorClassReflections->getNativeReflection();
            // this should avoid detecting @method as real method
            if (!$nativeClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethodReflection = $ancestorClassReflections->getNativeMethod($methodName);
            $parametersAcceptor = $parentClassMethodReflection->getVariants()[0];
            if (!$parametersAcceptor instanceof \PHPStan\Reflection\FunctionVariantWithPhpDocs) {
                continue;
            }
            // here we count only on strict types, not on docs
            return !$parametersAcceptor->getNativeReturnType() instanceof \PHPStan\Type\MixedType;
        }
        return \false;
    }
}
