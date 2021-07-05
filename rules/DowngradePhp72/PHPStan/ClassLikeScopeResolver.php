<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\PHPStan;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
final class ClassLikeScopeResolver
{
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector $traitNodeScopeCollector, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolveScope(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Analyser\Scope
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($scope instanceof \PHPStan\Analyser\Scope) {
            return $scope;
        }
        // fallback to a trait method
        $classLike = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if ($classLike instanceof \PhpParser\Node\Stmt\Trait_) {
            $traitName = $this->nodeNameResolver->getName($classLike);
            return $this->traitNodeScopeCollector->getScopeForTrait($traitName);
        }
        return null;
    }
}
