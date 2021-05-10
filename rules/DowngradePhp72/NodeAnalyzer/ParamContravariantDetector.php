<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

final class ParamContravariantDetector
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeRepository $nodeRepository
    ) {
    }

    public function hasParentMethod(ClassMethod $classMethod, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($classReflection === $ancestorClassReflection) {
                continue;
            }

            $classMethodName = $this->nodeNameResolver->getName($classMethod);
            if ($ancestorClassReflection->hasMethod($classMethodName)) {
                return true;
            }
        }

        return false;
    }

    public function hasChildMethod(ClassMethod $classMethod, Scope $classScope): bool
    {
        $classReflection = $classScope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);

        $classLikes = $this->nodeRepository->findClassesAndInterfacesByType($classReflection->getName());
        foreach ($classLikes as $classLike) {
            $currentClassMethod = $classLike->getMethod($methodName);
            if ($currentClassMethod !== null) {
                return true;
            }
        }

        return false;
    }
}
