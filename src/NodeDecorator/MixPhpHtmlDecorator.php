<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class MixPhpHtmlDecorator
{
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(NodeRemover $nodeRemover, NodeComparator $nodeComparator)
    {
        $this->nodeRemover = $nodeRemover;
        $this->nodeComparator = $nodeComparator;
    }
    public function decorateBefore(Node $node) : void
    {
        $firstNodePreviousNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($firstNodePreviousNode instanceof InlineHTML && !$node instanceof InlineHTML) {
            // re-print InlineHTML is safe
            $firstNodePreviousNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
    }
    /**
     * @param Node[] $nodes
     */
    public function decorateAfter(Node $node, array $nodes) : void
    {
        if (!$node instanceof Nop) {
            return;
        }
        $firstNodeAfterNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (!$firstNodeAfterNode instanceof Node) {
            return;
        }
        if (!$firstNodeAfterNode instanceof InlineHTML) {
            return;
        }
        $stmt = $this->resolveAppendAfterNode($node, $nodes);
        if (!$stmt instanceof Node) {
            return;
        }
        if ($stmt instanceof InlineHTML) {
            return;
        }
        $nodeComments = [];
        foreach ($node->getComments() as $comment) {
            if ($comment instanceof Doc) {
                $nodeComments[] = new Comment($comment->getText(), $comment->getStartLine(), $comment->getStartFilePos(), $comment->getStartTokenPos(), $comment->getEndLine(), $comment->getEndFilePos(), $comment->getEndTokenPos());
                continue;
            }
            $nodeComments[] = $comment;
        }
        $stmt->setAttribute(AttributeKey::COMMENTS, $nodeComments);
        // re-print InlineHTML is safe
        $firstNodeAfterNode->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        // remove Nop is marked  as comment of Next Node
        $this->nodeRemover->removeNode($node);
    }
    /**
     * @param Node[] $nodes
     */
    private function resolveAppendAfterNode(Nop $nop, array $nodes) : ?Node
    {
        foreach ($nodes as $key => $subNode) {
            if (!$this->nodeComparator->areNodesEqual($subNode, $nop)) {
                continue;
            }
            if (!isset($nodes[$key + 1])) {
                continue;
            }
            return $nodes[$key + 1];
        }
        return null;
    }
}
