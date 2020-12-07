<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Comment;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class CommentsMerger
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @param Node[] $mergedNodes
     */
    public function keepComments(Node $newNode, array $mergedNodes): void
    {
        $comments = $newNode->getComments();

        foreach ($mergedNodes as $mergedNode) {
            $comments = array_merge($comments, $mergedNode->getComments());
        }

        if ($comments === []) {
            return;
        }

        $newNode->setAttribute(AttributeKey::COMMENTS, $comments);

        // remove so comments "win"
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }

    public function keepParent(Node $newNode, Node $oldNode): void
    {
        $parent = $oldNode->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent === null) {
            return;
        }

        $arrayPhpDocInfo = $parent->getAttribute(AttributeKey::PHP_DOC_INFO);
        $arrayComments = $parent->getComments();

        if ($arrayPhpDocInfo === null && $arrayComments === []) {
            return;
        }

        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $arrayPhpDocInfo);
        $newNode->setAttribute(AttributeKey::COMMENTS, $arrayComments);
    }

    public function keepChildren(Node $newNode, Node $oldNode): void
    {
        $childrenComments = $this->collectChildrenComments($oldNode);

        if ($childrenComments === []) {
            return;
        }

        $commentContent = '';
        foreach ($childrenComments as $comment) {
            $commentContent .= $comment->getText() . PHP_EOL;
        }

        $newNode->setAttribute(AttributeKey::COMMENTS, [new Comment($commentContent)]);
    }

    /**
     * @return Comment[]
     */
    private function collectChildrenComments(Node $node): array
    {
        $childrenComments = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            &$childrenComments
        ): void {
            $comments = $node->getComments();

            if ($comments !== []) {
                $childrenComments = array_merge($childrenComments, $comments);
            }
        });

        return $childrenComments;
    }
}
