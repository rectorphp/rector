<?php

declare(strict_types=1);

namespace Rector\Comments;

use PhpParser\Node;
use Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;

/**
 * @see \Rector\Comments\Tests\CommentRemover\CommentRemoverTest
 */
final class CommentRemover
{
    /**
     * @var CommentRemovingNodeTraverser
     */
    private $commentRemovingNodeTraverser;

    public function __construct(CommentRemovingNodeTraverser $commentRemovingNodeTraverser)
    {
        $this->commentRemovingNodeTraverser = $commentRemovingNodeTraverser;
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
}
