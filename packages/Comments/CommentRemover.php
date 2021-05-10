<?php

declare(strict_types=1);

namespace Rector\Comments;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Comments\Tests\CommentRemover\CommentRemoverTest
 */
final class CommentRemover
{
    public function __construct(
        private CommentRemovingNodeTraverser $commentRemovingNodeTraverser
    ) {
    }

    /**
     * @param Node[]|Node|null $node
     * @return Node[]|null
     */
    public function removeFromNode($node): ?array
    {
        if ($node === null) {
            return null;
        }

        $copiedNodes = $node;

        $nodes = is_array($copiedNodes) ? $copiedNodes : [$copiedNodes];
        return $this->commentRemovingNodeTraverser->traverse($nodes);
    }

    public function rollbackComments(Node $node, Comment $comment): void
    {
        $node->setAttribute(AttributeKey::COMMENTS, null);
        $node->setDocComment(new Doc($comment->getText()));
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }
}
