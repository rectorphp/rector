<?php

declare (strict_types=1);
namespace Rector\Comments;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\Comments\NodeTraverser\CommentRemovingNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function rollbackComments(\PhpParser\Node $node, \PhpParser\Comment $comment) : void
    {
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, null);
        $node->setDocComment(new \PhpParser\Comment\Doc($comment->getText()));
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, null);
    }
}
