<?php

declare (strict_types=1);
namespace Rector\Defluent\ConflictGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasParentMethodOutsideVendor(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $methodName = $classMethod->name->toString();
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($classReflection === $ancestorClassReflection) {
                continue;
            }
            if (!$ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }
            $fileName = $ancestorClassReflection->getFileName();
            // PHP internal class
            if ($fileName === \false) {
                continue;
            }
            if (\strpos($fileName, '/vendor/') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    public function isReturnTypeChangeAllowed(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \true;
        }
        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        // probably internal
        if ($fileName === \false) {
            return \false;
        }
        return \strpos($fileName, '/vendor/') === \false;
    }
    private function getParentClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Reflection\MethodReflection
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($classReflection->getAncestors() as $parentClassReflection) {
            if ($classReflection === $parentClassReflection) {
                continue;
            }
            if (!$parentClassReflection->hasMethod($methodName)) {
                continue;
            }
            return $parentClassReflection->getNativeMethod($methodName);
        }
        return null;
    }
}
