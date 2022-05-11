<?php

declare (strict_types=1);
namespace Rector\Comments;

use PhpParser\Node;
use Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;
/**
 * @see \Rector\Tests\Comments\CommentRemover\CommentRemoverTest
 */
final class CommentRemover
{
    /**
     * @readonly
     * @var \Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser
     */
    private $commentRemovingNodeTraverser;
    public function __construct(\Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser $commentRemovingNodeTraverser)
    {
        $this->commentRemovingNodeTraverser = $commentRemovingNodeTraverser;
    }
    /**
     * @return mixed[]|null
     * @param mixed[]|\PhpParser\Node|null $node
     */
    public function removeFromNode($node)
    {
        if ($node === null) {
            return null;
        }
        $copiedNodes = $node;
        $nodes = \is_array($copiedNodes) ? $copiedNodes : [$copiedNodes];
        return $this->commentRemovingNodeTraverser->traverse($nodes);
    }
}
