<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
final class ControllerMethodAnalyzer
{
    /**
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
        $parentClassName = (string) $this->parentClassScopeResolver->resolveParentClassName($node);
        if (\substr_compare($parentClassName, 'Controller', -\strlen('Controller')) === 0) {
            return \true;
        }
        if (\substr_compare((string) $node->name, 'Action', -\strlen('Action')) === 0) {
            return \true;
        }
        return $node->isPublic();
    }
}
