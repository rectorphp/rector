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
use PhpParser\NodeFinder;
use PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeManipulator\BinaryOpManipulator;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\ForeachToInArrayRector\ForeachToInArrayRectorTest
 */
final class ForeachToInArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    public function __construct(BinaryOpManipulator $binaryOpManipulator, ValueResolver $valueResolver, NodeFinder $nodeFinder)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->valueResolver = $valueResolver;
        $this->nodeFinder = $nodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify `foreach` loops into `in_array` when possible', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            $prevStmt = $node->stmts[$key - 1] ?? null;
            if (!$prevStmt instanceof Foreach_) {
                continue;
            }
            $return = $stmt;
            $foreach = $prevStmt;
            if ($this->shouldSkipForeach($foreach)) {
                return null;
            }
            /** @var If_ $firstNodeInsideForeach */
            $firstNodeInsideForeach = $foreach->stmts[0];
            if ($this->shouldSkipIf($firstNodeInsideForeach)) {
                return null;
            }
            /** @var Identical|Equal $ifCondition */
            $ifCondition = $firstNodeInsideForeach->cond;
            $twoNodeMatch = $this->matchNodes($ifCondition, $foreach->valueVar);
            if (!$twoNodeMatch instanceof TwoNodeMatch) {
                return null;
            }
            $variableNodes = $this->nodeFinder->findInstanceOf($twoNodeMatch->getSecondExpr(), Variable::class);
            foreach ($variableNodes as $variableNode) {
                if ($this->nodeComparator->areNodesEqual($variableNode, $foreach->valueVar)) {
                    return null;
                }
            }
            $comparedExpr = $twoNodeMatch->getSecondExpr();
            if (!$this->isIfBodyABoolReturnNode($firstNodeInsideForeach)) {
                return null;
            }
            $foreachReturn = $firstNodeInsideForeach->stmts[0];
            if (!$foreachReturn instanceof Return_) {
                return null;
            }
            if (!$return->expr instanceof Expr) {
                return null;
            }
            if (!$this->valueResolver->isTrueOrFalse($return->expr)) {
                return null;
            }
            if (!$foreachReturn->expr instanceof Expr) {
                return null;
            }
            // cannot be "return true;" + "return true;"
            if ($this->nodeComparator->areNodesEqual($return, $foreachReturn)) {
                return null;
            }
            // 1. remove foreach
            unset($node->stmts[$key - 1]);
            // 2. make return of in_array()
            $funcCall = $this->createInArrayFunction($comparedExpr, $ifCondition, $foreach);
            $return = $this->createReturn($foreachReturn->expr, $funcCall);
            $node->stmts[$key] = $return;
            return $node;
        }
        return null;
    }
    private function shouldSkipForeach(Foreach_ $foreach) : bool
    {
        if ($foreach->keyVar instanceof Expr) {
            return \true;
        }
        if (\count($foreach->stmts) > 1) {
            return \true;
        }
        if (!$foreach->stmts[0] instanceof If_) {
            return \true;
        }
        $foreachValueStaticType = $this->getType($foreach->expr);
        return $foreachValueStaticType instanceof ObjectType;
    }
    private function shouldSkipIf(If_ $if) : bool
    {
        $ifCondition = $if->cond;
        if ($ifCondition instanceof Identical) {
            return \false;
        }
        return !$ifCondition instanceof Equal;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\Identical $binaryOp
     */
    private function matchNodes($binaryOp, Expr $expr) : ?TwoNodeMatch
    {
        return $this->binaryOpManipulator->matchFirstAndSecondConditionNode($binaryOp, Variable::class, function (Node $node, Node $otherNode) use($expr) : bool {
            return $this->nodeComparator->areNodesEqual($otherNode, $expr);
        });
    }
    private function isIfBodyABoolReturnNode(If_ $if) : bool
    {
        $ifStatment = $if->stmts[0];
        if (!$ifStatment instanceof Return_) {
            return \false;
        }
        if (!$ifStatment->expr instanceof Expr) {
            return \false;
        }
        return $this->valueResolver->isTrueOrFalse($ifStatment->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\Equal $binaryOp
     */
    private function createInArrayFunction(Expr $expr, $binaryOp, Foreach_ $foreach) : FuncCall
    {
        $arguments = $this->nodeFactory->createArgs([$expr, $foreach->expr]);
        if ($binaryOp instanceof Identical) {
            $arguments[] = $this->nodeFactory->createArg($this->nodeFactory->createTrue());
        }
        return $this->nodeFactory->createFuncCall('in_array', $arguments);
    }
    private function createReturn(Expr $expr, FuncCall $funcCall) : Return_
    {
        $expr = $this->valueResolver->isFalse($expr) ? new BooleanNot($funcCall) : $funcCall;
        return new Return_($expr);
    }
}
