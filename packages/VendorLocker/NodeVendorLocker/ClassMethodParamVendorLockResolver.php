<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\Reflection\ClassReflectionAncestorAnalyzer;
use Rector\VendorLocker\Reflection\MethodReflectionContractAnalyzer;

final class ClassMethodParamVendorLockResolver
{
    /**
     * @var ClassReflectionAncestorAnalyzer
     */
    private $classReflectionAncestorAnalyzer;

    /**
     * @var MethodReflectionContractAnalyzer
     */
    private $methodReflectionContractAnalyzer;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        ClassReflectionAncestorAnalyzer $classReflectionAncestorAnalyzer,
        MethodReflectionContractAnalyzer $methodReflectionContractAnalyzer,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository
    ) {
        $this->classReflectionAncestorAnalyzer = $classReflectionAncestorAnalyzer;
        $this->methodReflectionContractAnalyzer = $methodReflectionContractAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
    }

    public function isVendorLocked(ClassMethod $classMethod, int $paramPosition): bool
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        if ($classMethod->isMagic()) {
            return true;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            // skip self
            if ($ancestorClassReflection === $classReflection) {
                continue;
            }

            if (! $ancestorClassReflection->hasNativeMethod($methodName)) {
                continue;
            }

            // class is vendor, its locking us
            $classLike = $this->nodeRepository->findClassLike($ancestorClassReflection->getName());
            if ($classLike === null) {
                return true;
            }

            $classMethod = $classLike->getMethod($methodName);
            if ($classMethod === null) {
                continue;
            }

            $paramType = $classMethod->params[$paramPosition]->type;
            return $paramType !== null;
        }

        return false;
    }
}
