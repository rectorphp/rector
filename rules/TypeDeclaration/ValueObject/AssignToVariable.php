<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
final class AssignToVariable
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @readonly
     * @var string
     */
    private $variableName;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $assignedExpr;
    public function __construct(Variable $variable, string $variableName, Expr $assignedExpr)
    {
        $this->variable = $variable;
        $this->variableName = $variableName;
        $this->assignedExpr = $assignedExpr;
    }
    public function getVariable() : Variable
    {
        return $this->variable;
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
