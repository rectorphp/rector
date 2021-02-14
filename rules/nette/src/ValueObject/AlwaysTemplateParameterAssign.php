<?php

declare(strict_types=1);

namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;

final class AlwaysTemplateParameterAssign
{
    /**
     * @var Assign
     */
    private $assign;

    /**
     * @var string
     */
    private $parameterName;

    /**
     * @var Expr
     */
    private $assignedExpr;

    public function __construct(Assign $assign, string $parameterName, Expr $assignedExpr)
    {
        $this->assign = $assign;
        $this->parameterName = $parameterName;
        $this->assignedExpr = $assignedExpr;
    }

    public function getAssign(): Assign
    {
        return $this->assign;
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function getAssignedExpr(): Expr
    {
        return $this->assignedExpr;
    }
}
