<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix202304\Symfony\Contracts\Service\Attribute\Required;
final class MixPhpHtmlDecorator
{
    /**
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @var bool
     */
    private $isRequireReprintInlineHTML = \false;
    /**
     * @required
     */
    public function autowire(NodeRemover $nodeRemover) : void
    {
        $this->nodeRemover = $nodeRemover;
    }
    public function isRequireReprintInlineHTML() : bool
    {
        return $this->isRequireReprintInlineHTML;
    }
    public function disableIsRequireReprintInlineHTML() : void
    {
        $this->isRequireReprintInlineHTML = \false;
    }
    /**
     * @param array<Node|null> $nodes
     */
    public function decorateInlineHTML(InlineHTML $inlineHTML, int $key, array $nodes) : void
    {
        if (isset($nodes[$key - 1]) && !$nodes[$key - 1] instanceof InlineHTML && $nodes[$key - 1] instanceof Stmt) {
            $this->rePrintInlineHTML($inlineHTML, $nodes[$key - 1]);
        }
        if (!isset($nodes[$key + 1])) {
            return;
        }
        if ($nodes[$key + 1] instanceof InlineHTML) {
            return;
        }
        if (!$nodes[$key + 1] instanceof Stmt) {
            return;
        }
        $this->rePrintInlineHTML($inlineHTML, $nodes[$key + 1]);
    }
    /**
     * @param array<Node|null> $nodes
     */
    public function decorateAfterNop(Nop $nop, int $key, array $nodes) : void
    {
        if (!isset($nodes[$key + 1]) || $nodes[$key + 1] instanceof InlineHTML) {
            return;
        }
        if (!$nodes[$key + 1] instanceof Stmt) {
            return;
        }
        $firstNodeAfterNop = $nodes[$key + 1];
        if ($firstNodeAfterNop->getStartTokenPos() >= 0) {
            return;
        }
        // Token start = -1, just added
        $nodeComments = [];
        foreach ($nop->getComments() as $comment) {
            if ($comment instanceof Doc) {
                $nodeComments[] = new Comment($comment->getText(), $comment->getStartLine(), $comment->getStartFilePos(), $comment->getStartTokenPos(), $comment->getEndLine(), $comment->getEndFilePos(), $comment->getEndTokenPos());
                continue;
            }
            $nodeComments[] = $comment;
        }
        $firstNodeAfterNop->setAttribute(AttributeKey::COMMENTS, $nodeComments);
        // remove Nop is marked  as comment of Next Node
        $this->nodeRemover->removeNode($nop);
        $this->isRequireReprintInlineHTML = \true;
    }
    private function rePrintInlineHTML(InlineHTML $inlineHTML, Stmt $stmt) : void
    {
        // Token start = -1, just added
        if ($stmt->getStartTokenPos() < 0) {
            $inlineHTML->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->isRequireReprintInlineHTML = \true;
            return;
        }
        $originalNode = $stmt->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalNode instanceof Node) {
            return;
        }
        $node = $originalNode->getAttribute(AttributeKey::PARENT_NODE);
        if (!$node instanceof Stmt) {
            return;
        }
        $parentInlineHTML = $inlineHTML->getAttribute(AttributeKey::PARENT_NODE);
        // last Stmt that connected to InlineHTML just removed detected by different start token pos
        if ($parentInlineHTML instanceof Stmt && $parentInlineHTML->getStartTokenPos() !== $node->getStartTokenPos()) {
            $inlineHTML->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->isRequireReprintInlineHTML = \true;
        }
    }
}
