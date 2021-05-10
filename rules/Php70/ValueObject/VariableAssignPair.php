<?php

declare (strict_types=1);
namespace Rector\Php70\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
final class VariableAssignPair
{
    /**
     * @var \PhpParser\Node
     */
    private $variable;
    /**
     * @var \PhpParser\Node
     */
    private $assign;
    /**
     * @param Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch $variable
     * @param Assign|AssignOp|AssignRef $assign
     */
    public function __construct(\PhpParser\Node $variable, \PhpParser\Node $assign)
    {
        $this->variable = $variable;
        $this->assign = $assign;
    }
    /**
     * @return Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch
     */
    public function getVariable() : \PhpParser\Node\Expr
    {
        return $this->variable;
    }
    /**
     * @return Assign|AssignOp|AssignRef
     */
    public function getAssign() : \PhpParser\Node\Expr
    {
        return $this->assign;
    }
}
