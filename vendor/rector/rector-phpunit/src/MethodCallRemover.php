<?php

declare (strict_types=1);
namespace Rector\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class MethodCallRemover
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function removeMethodCall(Expression $expression, string $methodName) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($expression, function (Node $node) use($methodName) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, $methodName)) {
                return null;
            }
            return $node->var;
        });
    }
}
