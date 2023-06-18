<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ContextNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof For_ || $node instanceof Foreach_ || $node instanceof While_ || $node instanceof Do_) {
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node->stmts, static function (Node $subNode) {
                if ($subNode instanceof If_ || $subNode instanceof Break_) {
                    $subNode->setAttribute(AttributeKey::IS_IN_LOOP, \true);
                }
                return null;
            });
            return null;
        }
        return null;
    }
}
