<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ControllerMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    public function __construct(\Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    /**
     * Detect if is <some>Action() in Controller
     */
    public function isAction(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $parentClassName = (string) $this->parentClassScopeResolver->resolveParentClassName($scope);
        if (\substr_compare($parentClassName, 'Controller', -\strlen('Controller')) === 0) {
            return \true;
        }
        if (\substr_compare((string) $node->name, 'Action', -\strlen('Action')) === 0) {
            return \true;
        }
        return $node->isPublic();
    }
}
