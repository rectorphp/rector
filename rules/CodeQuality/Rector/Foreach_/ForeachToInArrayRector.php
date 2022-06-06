<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\ForeachToInArrayRector\ForeachToInArrayRectorTest
 */
final class ForeachToInArrayRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    public function __construct(\Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator, \Rector\BetterPhpDocParser\Comment\CommentsMerger $commentsMerger)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->commentsMerger = $commentsMerger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify `foreach` loops into `in_array` when possible', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
foreach ($items as $item) {
    if ($item === 'something') {
        return true;
    }
}

return false;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return in_array('something', $items, true);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipForeach($node)) {
            return null;
        }
        /** @var If_ $firstNodeInsideForeach */
        $firstNodeInsideForeach = $node->stmts[0];
        if ($this->shouldSkipIf($firstNodeInsideForeach)) {
            return null;
        }
        /** @var Identical|Equal $ifCondition */
        $ifCondition = $firstNodeInsideForeach->cond;
        $foreachValueVar = $node->valueVar;
        $twoNodeMatch = $this->matchNodes($ifCondition, $foreachValueVar);
        if (!$twoNodeMatch instanceof \Rector\Php71\ValueObject\TwoNodeMatch) {
            return null;
        }
        $comparedNode = $twoNodeMatch->getSecondExpr();
        if (!$this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
            return null;
        }
        $funcCall = $this->createInArrayFunction($comparedNode, $ifCondition, $node);
        /** @var Return_ $returnToRemove */
        $returnToRemove = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        /** @var Return_ $return */
        $return = $firstNodeInsideForeach->stmts[0];
        if ($returnToRemove->expr === null) {
            return null;
        }
        if (!$this->valueResolver->isTrueOrFalse($returnToRemove->expr)) {
            return null;
        }
        $returnedExpr = $return->expr;
        if (!$returnedExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        // cannot be "return true;" + "return true;"
        if ($this->nodeComparator->areNodesEqual($return, $returnToRemove)) {
            return null;
        }
        $this->removeNode($returnToRemove);
        $return = $this->createReturn($returnedExpr, $funcCall);
        $this->commentsMerger->keepChildren($return, $node);
        return $return;
    }
    private function shouldSkipForeach(\PhpParser\Node\Stmt\Foreach_ $foreach) : bool
    {
        if ($foreach->keyVar !== null) {
            return \true;
        }
        if (\count($foreach->stmts) > 1) {
            return \true;
        }
        $nextNode = $foreach->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node) {
            return \true;
        }
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Return_) {
            return \true;
        }
        $returnExpression = $nextNode->expr;
        if (!$returnExpression instanceof \PhpParser\Node\Expr) {
            return \true;
        }
        if (!$this->valueResolver->isTrueOrFalse($returnExpression)) {
            return \true;
        }
        $foreachValueStaticType = $this->getType($foreach->expr);
        if ($foreachValueStaticType instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        return !$foreach->stmts[0] instanceof \PhpParser\Node\Stmt\If_;
    }
    private function shouldSkipIf(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $ifCondition = $if->cond;
        if ($ifCondition instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return \false;
        }
        return !$ifCondition instanceof \PhpParser\Node\Expr\BinaryOp\Equal;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\Identical $binaryOp
     */
    private function matchNodes($binaryOp, \PhpParser\Node\Expr $expr) : ?\Rector\Php71\ValueObject\TwoNodeMatch
    {
        return $this->binaryOpManipulator->matchFirstAndSecondConditionNode($binaryOp, \PhpParser\Node\Expr\Variable::class, function (\PhpParser\Node $node, \PhpParser\Node $otherNode) use($expr) : bool {
            return $this->nodeComparator->areNodesEqual($otherNode, $expr);
        });
    }
    private function isIfBodyABoolReturnNode(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $ifStatment = $if->stmts[0];
        if (!$ifStatment instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        if ($ifStatment->expr === null) {
            return \false;
        }
        return $this->valueResolver->isTrueOrFalse($ifStatment->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\Equal $binaryOp
     */
    private function createInArrayFunction(\PhpParser\Node\Expr $expr, $binaryOp, \PhpParser\Node\Stmt\Foreach_ $foreach) : \PhpParser\Node\Expr\FuncCall
    {
        $arguments = $this->nodeFactory->createArgs([$expr, $foreach->expr]);
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            $arguments[] = $this->nodeFactory->createArg($this->nodeFactory->createTrue());
        }
        return $this->nodeFactory->createFuncCall('in_array', $arguments);
    }
    private function createReturn(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Stmt\Return_
    {
        $expr = $this->valueResolver->isFalse($expr) ? new \PhpParser\Node\Expr\BooleanNot($funcCall) : $funcCall;
        return new \PhpParser\Node\Stmt\Return_($expr);
    }
}
