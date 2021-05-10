<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Type\MixedType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\Reflection\MethodReflectionContractAnalyzer;
final class ClassMethodReturnVendorLockResolver
{
    /**
     * @var MethodReflectionContractAnalyzer
     */
    private $methodReflectionContractAnalyzer;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\VendorLocker\Reflection\MethodReflectionContractAnalyzer $methodReflectionContractAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->methodReflectionContractAnalyzer = $methodReflectionContractAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isVendorLocked(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        if (\count($classReflection->getAncestors()) === 1) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->isVendorLockedByParentClass($classReflection, $methodName)) {
            return \true;
        }
        if ($classReflection->isTrait()) {
            return \false;
        }
        return $this->methodReflectionContractAnalyzer->hasInterfaceContract($classReflection, $methodName);
    }
    private function isVendorLockedByParentClass(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        foreach ($classReflection->getParents() as $parentClassReflections) {
            if (!$parentClassReflections->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethodReflection = $parentClassReflections->getNativeMethod($methodName);
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
