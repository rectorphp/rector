<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use PhpParser\Node\Expr;

final class AssignAndRootExpr
{
    /**
     * @var Expr
     */
    private $assignExpr;

    /**
     * @var Expr
     */
    private $rootExpr;

    public function __construct(Expr $assignExpr, Expr $rootExpr)
    {
        $this->assignExpr = $assignExpr;
        $this->rootExpr = $rootExpr;
    }

    public function getAssignExpr(): Expr
    {
        return $this->assignExpr;
    }

    public function getRootExpr(): Expr
    {
        return $this->rootExpr;
    }
}
