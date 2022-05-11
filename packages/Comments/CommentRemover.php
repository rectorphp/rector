<?php

declare(strict_types=1);

namespace Rector\Comments;

use PhpParser\Node;
use Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;

/**
 * @see \Rector\Tests\Comments\CommentRemover\CommentRemoverTest
 */
final class CommentRemover
{
    public function __construct(
        private readonly CommentRemovingNodeTraverser $commentRemovingNodeTraverser
    ) {
    }

    /**
     * @return Node[]|null
     */
    public function removeFromNode(array | Node | null $node): array | null
    {
        if ($node === null) {
            return null;
        }

        $copiedNodes = $node;

        $nodes = is_array($copiedNodes) ? $copiedNodes : [$copiedNodes];
        return $this->commentRemovingNodeTraverser->traverse($nodes);
    }
}
