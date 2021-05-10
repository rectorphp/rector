<?php

declare(strict_types=1);

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
     * @param Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch $variable
     * @param Assign|AssignOp|AssignRef $assign
     */
    public function __construct(
        private Node $variable,
        private Node $assign
    ) {
    }

    /**
     * @return Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch
     */
    public function getVariable(): Expr
    {
        return $this->variable;
    }

    /**
     * @return Assign|AssignOp|AssignRef
     */
    public function getAssign(): Expr
    {
        return $this->assign;
    }
}
