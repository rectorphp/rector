<?php

declare (strict_types=1);
namespace Rector\Php70\ValueObject;

use PhpParser\Node;
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
     * @var Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch
     * @readonly
     */
    private $variable;
    /**
     * @var Assign|AssignOp|AssignRef
     * @readonly
     */
    private $assign;
    /**
     * @param Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch $variable
     * @param Assign|AssignOp|AssignRef $assign
     */
    public function __construct(Node $variable, Node $assign)
    {
        $this->variable = $variable;
        $this->assign = $assign;
    }
    /**
     * @return \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\Variable
     */
    public function getVariable()
    {
        return $this->variable;
    }
    /**
     * @return \PhpParser\Node\Expr\Assign|\PhpParser\Node\Expr\AssignOp|\PhpParser\Node\Expr\AssignRef
     */
    public function getAssign()
    {
        return $this->assign;
    }
}
