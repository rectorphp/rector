<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\PHPStan;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;

final class ClassLikeScopeResolver
{
    public function __construct(
        private TraitNodeScopeCollector $traitNodeScopeCollector,
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    public function resolveScope(ClassMethod $classMethod): ?Scope
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope) {
            return $scope;
        }

        // fallback to a trait method
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike instanceof Trait_) {
            $traitName = $this->nodeNameResolver->getName($classLike);
            return $this->traitNodeScopeCollector->getScopeForTrait($traitName);
        }

        return null;
    }
}
