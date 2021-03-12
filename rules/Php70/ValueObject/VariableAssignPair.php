<?php

declare(strict_types=1);

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
     */
    private $variable;

    /**
     * @var Assign|AssignOp|AssignRef
     */
    private $assign;

    /**
     * @param Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch $variable
     * @param Assign|AssignOp|AssignRef $node
     */
    public function __construct(Node $variable, Node $node)
    {
        $this->variable = $variable;
        $this->assign = $node;
    }

    /**
     * @return Variable|ArrayDimFetch|PropertyFetch|StaticPropertyFetch
     */
    public function getVariable(): Node
    {
        return $this->variable;
    }

    /**
     * @return Assign|AssignOp|AssignRef
     */
    public function getAssign(): Node
    {
        return $this->assign;
    }
}
