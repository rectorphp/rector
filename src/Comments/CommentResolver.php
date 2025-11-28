<?php

declare (strict_types=1);
namespace Rector\Comments;

use PhpParser\Comment;
use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CommentResolver
{
    /**
     * @param int|float $rangeLine
     * @return float|int
     */
    public function resolveRangeLineFromComment($rangeLine, int $endLine, Stmt $nextStmt)
    {
        /** @var Comment[]|null $comments */
        $comments = $nextStmt->getAttribute(AttributeKey::COMMENTS);
        if ($this->hasNoComment($comments)) {
            return $rangeLine;
        }
        /** @var Comment[] $comments */
        $firstComment = $comments[0];
        $line = $firstComment->getStartLine();
        return $line - $endLine;
    }
    /**
     * @param Comment[]|null $comments
     */
    private function hasNoComment(?array $comments): bool
    {
        return $comments === null || $comments === [];
    }
}
