<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\NeverType;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CountManipulator
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isCounterHigherThanOne(Expr $firstExpr, Expr $secondExpr) : bool
    {
        // e.g. count($values) > 0
        if ($firstExpr instanceof Greater) {
            return $this->isGreater($firstExpr, $secondExpr);
        }
        // e.g. count($values) >= 1
        if ($firstExpr instanceof GreaterOrEqual) {
            return $this->isGreaterOrEqual($firstExpr, $secondExpr);
        }
        // e.g. 0 < count($values)
        if ($firstExpr instanceof Smaller) {
            return $this->isSmaller($firstExpr, $secondExpr);
        }
        // e.g. 1 <= count($values)
        if ($firstExpr instanceof SmallerOrEqual) {
            return $this->isSmallerOrEqual($firstExpr, $secondExpr);
        }
        return \false;
    }
    private function isGreater(Greater $greater, Expr $expr) : bool
    {
        if (!$this->isNumber($greater->right, 0)) {
            return \false;
        }
        return $this->isCountWithExpression($greater->left, $expr);
    }
    private function isGreaterOrEqual(GreaterOrEqual $greaterOrEqual, Expr $expr) : bool
    {
        if (!$this->isNumber($greaterOrEqual->right, 1)) {
            return \false;
        }
        return $this->isCountWithExpression($greaterOrEqual->left, $expr);
    }
    private function isSmaller(Smaller $smaller, Expr $expr) : bool
    {
        if (!$this->isNumber($smaller->left, 0)) {
            return \false;
        }
        return $this->isCountWithExpression($smaller->right, $expr);
    }
    private function isSmallerOrEqual(SmallerOrEqual $smallerOrEqual, Expr $expr) : bool
    {
        if (!$this->isNumber($smallerOrEqual->left, 1)) {
            return \false;
        }
        return $this->isCountWithExpression($smallerOrEqual->right, $expr);
    }
    private function isNumber(Expr $expr, int $value) : bool
    {
        if (!$expr instanceof LNumber) {
            return \false;
        }
        return $expr->value === $value;
    }
    private function isCountWithExpression(Expr $node, Expr $expr) : bool
    {
        if (!$node instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node, 'count')) {
            return \false;
        }
        if ($node->isFirstClassCallable()) {
            return \false;
        }
        if (!isset($node->getArgs()[0])) {
            return \false;
        }
        $countedExpr = $node->getArgs()[0]->value;
        if ($this->nodeComparator->areNodesEqual($countedExpr, $expr)) {
            $exprType = $this->nodeTypeResolver->getNativeType($expr);
            if (!$exprType->isArray()->yes()) {
                return $exprType instanceof NeverType;
            }
            return \true;
        }
        return \false;
    }
}
