<?php

declare (strict_types=1);
namespace Rector\Php71;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php71\ValueObject\TwoNodeMatch;
final class IsArrayAndDualCheckToAble
{
    /**
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function processBooleanOr(\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanOr, string $type, string $newMethodName) : ?\PhpParser\Node\Expr\FuncCall
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($booleanOr, \PhpParser\Node\Expr\Instanceof_::class, \PhpParser\Node\Expr\FuncCall::class);
        if (!$twoNodeMatch instanceof \Rector\Php71\ValueObject\TwoNodeMatch) {
            return null;
        }
        /** @var Instanceof_ $instanceOf */
        $instanceOf = $twoNodeMatch->getFirstExpr();
        /** @var FuncCall $funcCall */
        $funcCall = $twoNodeMatch->getSecondExpr();
        $instanceOfClass = $instanceOf->class;
        if ($instanceOfClass instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if ((string) $instanceOfClass !== $type) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($funcCall, 'is_array')) {
            return null;
        }
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        // both use same var
        if (!$funcCall->args[0]->value instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        /** @var Variable $firstVarNode */
        $firstVarNode = $funcCall->args[0]->value;
        if (!$instanceOf->expr instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        /** @var Variable $secondVarNode */
        $secondVarNode = $instanceOf->expr;
        // are they same variables
        if ($firstVarNode->name !== $secondVarNode->name) {
            return null;
        }
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name($newMethodName), [new \PhpParser\Node\Arg($firstVarNode)]);
    }
}
