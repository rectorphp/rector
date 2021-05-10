<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassLikeWithTraitsClassMethodResolver
{
    public function __construct(
        private NodeRepository $nodeRepository
    ) {
    }

    /**
     * @param Class_|Interface_ $classLike
     * @return ClassMethod[]
     */
    public function resolve(ClassLike $classLike): array
    {
        $scope = $classLike->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        $classMethods = [];
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $ancestorClassLike = $this->nodeRepository->findClassLike($ancestorClassReflection->getName());
            if (! $ancestorClassLike instanceof ClassLike) {
                continue;
            }

            $classMethods = array_merge($classMethods, $ancestorClassLike->getMethods());
        }

        return $classMethods;
    }
}
