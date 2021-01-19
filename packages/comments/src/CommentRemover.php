<?php

declare(strict_types=1);

namespace Rector\Comments;

use PhpParser\Node;
use Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;

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
     * @return Node[]|Node|null
     */
    public function removeFromNode($node)
    {
        if ($node === null) {
            return null;
        }

        if (! is_array($node)) {
            $nodes = [$node];
        } else {
            $nodes = $node;
        }

        return $this->commentRemovingNodeTraverser->traverse($nodes);
    }
}
