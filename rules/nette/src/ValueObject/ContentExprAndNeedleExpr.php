<?php

declare(strict_types=1);

namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;

final class ContentExprAndNeedleExpr
{
    /**
     * @var Expr
     */
    private $contentExpr;

    /**
     * @var Expr
     */
    private $needleExpr;

    public function __construct(Expr $contentExpr, Expr $needleExpr)
    {
        $this->contentExpr = $contentExpr;
        $this->needleExpr = $needleExpr;
    }

    public function getContentExpr(): Expr
    {
        return $this->contentExpr;
    }

    public function getNeedleExpr(): Expr
    {
        return $this->needleExpr;
    }
}
