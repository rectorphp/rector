<?php

declare (strict_types=1);
namespace Rector\Defluent\ConflictGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeRepository $nodeRepository, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasParentMethodOutsideVendor(ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
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
            $parentClassMethodReflection = $ancestorClassReflection->getMethod($methodName, $scope);
            $parentClassMethod = $this->nodeRepository->findClassMethodByMethodReflection($parentClassMethodReflection);
            if (!$parentClassMethod instanceof ClassMethod) {
                return \true;
            }
        }
        return \false;
    }
    public function isReturnTypeChangeAllowed(ClassMethod $classMethod) : bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);
        // nothign to check
        if (!$parentClassMethodReflection instanceof MethodReflection) {
            return \true;
        }
        $parentClassMethod = $this->nodeRepository->findClassMethodByMethodReflection($parentClassMethodReflection);
        // if null, we're unable to override → skip it
        if (!$parentClassMethod instanceof ClassMethod) {
            return \true;
        }
        return $parentClassMethod->returnType === null;
    }
    private function getParentClassMethod(ClassMethod $classMethod) : ?MethodReflection
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
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
