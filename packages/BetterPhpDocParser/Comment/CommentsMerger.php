<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Comment;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class CommentsMerger
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    /**
     * @param Node[] $mergedNodes
     */
    public function keepComments(\PhpParser\Node $newNode, array $mergedNodes) : void
    {
        $comments = $newNode->getComments();
        foreach ($mergedNodes as $mergedNode) {
            $comments = \array_merge($comments, $mergedNode->getComments());
        }
        if ($comments === []) {
            return;
        }
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $comments);
        // remove so comments "win"
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, null);
    }
    public function keepParent(\PhpParser\Node $newNode, \PhpParser\Node $oldNode) : void
    {
        $parent = $oldNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return;
        }
        $phpDocInfo = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO);
        $comments = $parent->getComments();
        if ($phpDocInfo === null && $comments === []) {
            return;
        }
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $comments);
    }
    public function keepChildren(\PhpParser\Node $newNode, \PhpParser\Node $oldNode) : void
    {
        $childrenComments = $this->collectChildrenComments($oldNode);
        if ($childrenComments === []) {
            return;
        }
        $commentContent = '';
        foreach ($childrenComments as $childComment) {
            $commentContent .= $childComment->getText() . \PHP_EOL;
        }
        $newNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, [new \PhpParser\Comment($commentContent)]);
    }
    /**
     * @return Comment[]
     */
    private function collectChildrenComments(\PhpParser\Node $node) : array
    {
        $childrenComments = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, function (\PhpParser\Node $node) use(&$childrenComments) : void {
            $comments = $node->getComments();
            if ($comments !== []) {
                $childrenComments = \array_merge($childrenComments, $comments);
            }
        });
        return $childrenComments;
    }
}
