<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Type\MixedType;
use Rector\NodeAnalyzer\MagicClassMethodAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Reflection\ReflectionResolver;
final class ClassMethodReturnVendorLockResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\MagicClassMethodAnalyzer
     */
    private $magicClassMethodAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, MagicClassMethodAnalyzer $magicClassMethodAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->magicClassMethodAnalyzer = $magicClassMethodAnalyzer;
    }
    public function isVendorLocked(ClassMethod $classMethod) : bool
    {
        if ($this->magicClassMethodAnalyzer->isUnsafeOverridden($classMethod)) {
            return \true;
        }
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->isVendorLockedByAncestors($classReflection, $methodName);
    }
    private function isVendorLockedByAncestors(ClassReflection $classReflection, string $methodName) : bool
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
            if (!$ancestorClassReflections->hasNativeMethod($methodName)) {
                continue;
            }
            $parentClassMethodReflection = $ancestorClassReflections->getNativeMethod($methodName);
            $parametersAcceptor = $parentClassMethodReflection->getVariants()[0];
            if (!$parametersAcceptor instanceof FunctionVariantWithPhpDocs) {
                continue;
            }
            // here we count only on strict types, not on docs
            return !$parametersAcceptor->getNativeReturnType() instanceof MixedType;
        }
        return \false;
    }
}
