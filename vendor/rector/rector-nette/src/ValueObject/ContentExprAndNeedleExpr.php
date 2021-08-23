<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
final class ContentExprAndNeedleExpr
{
    /**
     * @var \PhpParser\Node\Expr
     */
    private $contentExpr;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $needleExpr;
    public function __construct(\PhpParser\Node\Expr $contentExpr, \PhpParser\Node\Expr $needleExpr)
    {
        $this->contentExpr = $contentExpr;
        $this->needleExpr = $needleExpr;
    }
    public function getContentExpr() : \PhpParser\Node\Expr
    {
        return $this->contentExpr;
    }
    public function getNeedleExpr() : \PhpParser\Node\Expr
    {
        return $this->needleExpr;
    }
}
