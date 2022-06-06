<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\ValueObject;

use RectorPrefix20220606\PhpParser\Comment;
use RectorPrefix20220606\PhpParser\Node\Expr;
final class KeyAndExpr
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr|null
     */
    private $keyExpr;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    /**
     * @var Comment[]
     * @readonly
     */
    private $comments;
    /**
     * @param Comment[] $comments
     */
    public function __construct(?Expr $keyExpr, Expr $expr, array $comments)
    {
        $this->keyExpr = $keyExpr;
        $this->expr = $expr;
        $this->comments = $comments;
    }
    public function getKeyExpr() : ?Expr
    {
        return $this->keyExpr;
    }
    public function getExpr() : Expr
    {
        return $this->expr;
    }
    /**
     * @return Comment[]
     */
    public function getComments() : array
    {
        return $this->comments;
    }
}
