<?php

declare (strict_types=1);
namespace Rector\CodeQuality\ValueObject;

use PhpParser\Comment;
use PhpParser\Node\Expr;
final class KeyAndExpr
{
    /**
     * @readonly
     */
    private ?Expr $keyExpr;
    /**
     * @readonly
     */
    private Expr $expr;
    /**
     * @var Comment[]
     * @readonly
     */
    private array $comments;
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
