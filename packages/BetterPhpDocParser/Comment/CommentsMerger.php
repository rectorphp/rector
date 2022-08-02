<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Comment;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class CommentsMerger
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @param Node[] $mergedNodes
     */
    public function keepComments(Node $newNode, array $mergedNodes) : void
    {
        $comments = $newNode->getComments();
        foreach ($mergedNodes as $mergedNode) {
            $comments = \array_merge($comments, $mergedNode->getComments());
        }
        if ($comments === []) {
            return;
        }
        $newNode->setAttribute(AttributeKey::COMMENTS, $comments);
        // remove so comments "win"
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }
    /**
     * @api
     */
    public function keepParent(Node $newNode, Node $oldNode) : void
    {
        $parent = $oldNode->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Node) {
            return;
        }
        $phpDocInfo = $parent->getAttribute(AttributeKey::PHP_DOC_INFO);
        $comments = $parent->getComments();
        if ($phpDocInfo === null && $comments === []) {
            return;
        }
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        $newNode->setAttribute(AttributeKey::COMMENTS, $comments);
    }
    /**
     * @api
     */
    public function keepChildren(Node $newNode, Node $oldNode) : void
    {
        $childrenComments = $this->collectChildrenComments($oldNode);
        if ($childrenComments === []) {
            return;
        }
        $commentContent = '';
        foreach ($childrenComments as $childComment) {
            $commentContent .= $childComment->getText() . \PHP_EOL;
        }
        $newNode->setAttribute(AttributeKey::COMMENTS, [new Comment($commentContent)]);
    }
    /**
     * @return Comment[]
     */
    private function collectChildrenComments(Node $node) : array
    {
        $childrenComments = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, static function (Node $node) use(&$childrenComments) {
            $comments = $node->getComments();
            if ($comments !== []) {
                $childrenComments = \array_merge($childrenComments, $comments);
            }
            return null;
        });
        return $childrenComments;
    }
}
