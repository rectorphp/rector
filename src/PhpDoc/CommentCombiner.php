<?php

declare(strict_types=1);

namespace Rector\Core\PhpDoc;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class CommentCombiner
{
    /**
     * @var Comment[]
     */
    private $comments = [];

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function combineCommentsToNode(Node $originalNode, Node $newNode): void
    {
        $this->comments = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($originalNode, function (Node $node): void {
            if ($node->hasAttribute('comments')) {
                $this->comments = array_merge($this->comments, $node->getComments());
            }
        });

        if ($this->comments === []) {
            return;
        }

        $commentContent = '';
        foreach ($this->comments as $comment) {
            $commentContent .= $comment->getText() . PHP_EOL;
        }

        $newNode->setAttribute(AttributeKey::COMMENTS, [new Comment($commentContent)]);
    }
}
