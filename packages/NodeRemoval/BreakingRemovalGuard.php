<?php

declare (strict_types=1);
namespace Rector\NodeRemoval;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class BreakingRemovalGuard
{
    public function ensureNodeCanBeRemove(Node $node) : void
    {
        if ($this->isLegalNodeRemoval($node)) {
            return;
        }
        // validate the node can be removed
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }
        throw new ShouldNotHappenException(\sprintf('Node "%s" on line %d is child of "%s", so it cannot be removed as it would break PHP code. Change or remove the parent node instead.', \get_class($node), $node->getLine(), \get_class($parentNode)));
    }
    /**
     * @api
     */
    public function isLegalNodeRemoval(Node $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof If_ && $parentNode->cond === $node) {
            return \false;
        }
        if ($parentNode instanceof BooleanNot) {
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
        if ($parentNode instanceof Assign) {
            return \false;
        }
        if ($this->isIfCondition($node)) {
            return \false;
        }
        return !$this->isWhileCondition($node);
    }
    private function isIfCondition(Node $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof If_) {
            return \false;
        }
        return $parentNode->cond === $node;
    }
    private function isWhileCondition(Node $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof While_) {
            return \false;
        }
        return $parentNode->cond === $node;
    }
}
