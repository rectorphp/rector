<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PhpParser\Node\Expr;
final class AssignToVariable
{
    /**
     * @readonly
     */
    private string $variableName;
    /**
     * @readonly
     */
    private Expr $assignedExpr;
    public function __construct(string $variableName, Expr $assignedExpr)
    {
        $this->variableName = $variableName;
        $this->assignedExpr = $assignedExpr;
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
    public function getAssignedExpr() : Expr
    {
        return $this->assignedExpr;
    }
}
