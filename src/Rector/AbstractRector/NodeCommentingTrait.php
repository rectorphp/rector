<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeCommentingTrait
{
    protected function addComment(Node $node, string $text): void
    {
        $comments = $node->getComments();
        $comments[] = new Comment($text);
        $node->setAttribute(AttributeKey::COMMENTS, $comments);
    }
}
