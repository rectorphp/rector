<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\Reflection\MethodReflectionContractAnalyzer;
final class ClassMethodVendorLockResolver
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
    /**
     * Checks for:
     * - interface required methods
     * - abstract classes reqired method
     *
     * Prevent:
     * - removing class methods, that breaks the code
     */
    public function isRemovalVendorLocked(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        if ($this->methodReflectionContractAnalyzer->hasInterfaceContract($classReflection, $classMethodName)) {
            return \true;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod($classMethodName)) {
                continue;
            }
            $methodReflection = $parentClassReflection->getNativeMethod($classMethodName);
            if (!$methodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
                continue;
            }
            if ($methodReflection->isAbstract()) {
                return \true;
            }
        }
        return \false;
    }
}
