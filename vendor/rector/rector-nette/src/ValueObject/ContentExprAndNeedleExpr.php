<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
final class ContentExprAndNeedleExpr
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $contentExpr;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $needleExpr;
    public function __construct(Expr $contentExpr, Expr $needleExpr)
    {
        $this->contentExpr = $contentExpr;
        $this->needleExpr = $needleExpr;
    }
    public function getContentExpr() : Expr
    {
        return $this->contentExpr;
    }
    public function getNeedleExpr() : Expr
    {
        return $this->needleExpr;
    }
}
