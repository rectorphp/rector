<?php

declare (strict_types=1);
namespace Rector\Php56\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast\Unset_ as UnsetCast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20210827\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class UndefinedVariableResolver
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\RectorPrefix20210827\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    public function resolve($node) : array
    {
        $undefinedVariables = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (\PhpParser\Node $node) use(&$undefinedVariables) : ?int {
            // entering new scope - break!
            if ($node instanceof \PhpParser\Node\FunctionLike && !$node instanceof \PhpParser\Node\Expr\ArrowFunction) {
                return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
            }
            if ($node instanceof \PhpParser\Node\Stmt\Foreach_) {
                // handled above
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if ($this->shouldSkipVariable($node)) {
                return null;
            }
            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($node);
            // defined 100 %
            /** @var Scope $scope */
            $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            if ($scope->hasVariableType($variableName)->yes()) {
                return null;
            }
            $undefinedVariables[] = $variableName;
            return null;
        });
        return \array_unique($undefinedVariables);
    }
    private function shouldSkipVariable(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $parentNode = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Stmt\Global_) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node && ($parentNode instanceof \PhpParser\Node\Expr\Assign || $parentNode instanceof \PhpParser\Node\Expr\AssignRef || $this->isStaticVariable($parentNode))) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Stmt\Unset_ || $parentNode instanceof \PhpParser\Node\Expr\Cast\Unset_) {
            return \true;
        }
        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return \true;
        }
        $nodeScope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$nodeScope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $variableName = $this->nodeNameResolver->getName($variable);
        // skip $this, as probably in outer scope
        if ($variableName === 'this') {
            return \true;
        }
        return $variableName === null;
    }
    private function isStaticVariable(\PhpParser\Node $parentNode) : bool
    {
        // definition of static variable
        if ($parentNode instanceof \PhpParser\Node\Stmt\StaticVar) {
            $parentParentNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof \PhpParser\Node\Stmt\Static_) {
                return \true;
            }
        }
        return \false;
    }
    private function isListAssign(\PhpParser\Node $node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\List_) {
            return \true;
        }
        return $parentNode instanceof \PhpParser\Node\Expr\Array_;
    }
}
