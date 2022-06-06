<?php

declare (strict_types=1);
namespace Rector\DeadCode;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class UselessIfCondBeforeForeachDetector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->paramAnalyzer = $paramAnalyzer;
    }
    /**
     * Matches:
     * !empty($values)
     */
    public function isMatchingNotEmpty(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr $foreachExpr) : bool
    {
        $cond = $if->cond;
        if (!$cond instanceof \PhpParser\Node\Expr\BooleanNot) {
            return \false;
        }
        if (!$cond->expr instanceof \PhpParser\Node\Expr\Empty_) {
            return \false;
        }
        /** @var Empty_ $empty */
        $empty = $cond->expr;
        if (!$this->nodeComparator->areNodesEqual($empty->expr, $foreachExpr)) {
            return \false;
        }
        // is array though?
        $arrayType = $this->nodeTypeResolver->getType($empty->expr);
        if (!$arrayType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        $previousParam = $this->fromPreviousParam($foreachExpr);
        if (!$previousParam instanceof \PhpParser\Node\Param) {
            return \true;
        }
        if ($this->paramAnalyzer->isNullable($previousParam)) {
            return \false;
        }
        return !$this->paramAnalyzer->hasDefaultNull($previousParam);
    }
    /**
     * Matches:
     * $values !== []
     * $values != []
     * [] !== $values
     * [] != $values
     */
    public function isMatchingNotIdenticalEmptyArray(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr $foreachExpr) : bool
    {
        if (!$if->cond instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical && !$if->cond instanceof \PhpParser\Node\Expr\BinaryOp\NotEqual) {
            return \false;
        }
        /** @var NotIdentical|NotEqual $notIdentical */
        $notIdentical = $if->cond;
        return $this->isMatchingNotBinaryOp($notIdentical, $foreachExpr);
    }
    private function fromPreviousParam(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node
    {
        return $this->betterNodeFinder->findFirstPrevious($expr, function (\PhpParser\Node $node) use($expr) : bool {
            if (!$node instanceof \PhpParser\Node\Param) {
                return \false;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\NotEqual $binaryOp
     */
    private function isMatchingNotBinaryOp($binaryOp, \PhpParser\Node\Expr $foreachExpr) : bool
    {
        if ($this->isEmptyArrayAndForeachedVariable($binaryOp->left, $binaryOp->right, $foreachExpr)) {
            return \true;
        }
        return $this->isEmptyArrayAndForeachedVariable($binaryOp->right, $binaryOp->left, $foreachExpr);
    }
    private function isEmptyArrayAndForeachedVariable(\PhpParser\Node\Expr $leftExpr, \PhpParser\Node\Expr $rightExpr, \PhpParser\Node\Expr $foreachExpr) : bool
    {
        if (!$this->isEmptyArray($leftExpr)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($foreachExpr, $rightExpr);
    }
    private function isEmptyArray(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Array_) {
            return \false;
        }
        return $expr->items === [];
    }
}
