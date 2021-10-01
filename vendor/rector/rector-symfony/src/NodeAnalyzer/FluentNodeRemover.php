<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FluentNodeRemover
{
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(\Rector\NodeRemoval\NodeRemover $nodeRemover)
    {
        $this->nodeRemover = $nodeRemover;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Stmt\Return_ $node
     */
    public function removeCurrentNode($node) : void
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Expr\Assign) {
            $this->nodeRemover->removeNode($parent);
            return;
        }
        // part of method call
        if ($parent instanceof \PhpParser\Node\Arg) {
            $parentParent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentParent instanceof \PhpParser\Node\Expr\MethodCall) {
                $this->nodeRemover->removeNode($parentParent);
            }
            return;
        }
        $this->nodeRemover->removeNode($node);
    }
}
