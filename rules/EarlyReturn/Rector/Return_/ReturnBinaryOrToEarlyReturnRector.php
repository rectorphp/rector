<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\ReturnBinaryOrToEarlyReturnRectorTest
 */
final class ReturnBinaryOrToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    /**
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\Core\PhpParser\Node\AssignAndBinaryMap $assignAndBinaryMap, \Rector\Core\NodeAnalyzer\CallAnalyzer $callAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->callAnalyzer = $callAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes Single return of || to early returns', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function accept()
    {
        return $this->something() || $this->somethingElse();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return (bool) $this->somethingElse();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     * @return null|Node[]
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return null;
        }
        /** @var BooleanOr $booleanOr */
        $booleanOr = $node->expr;
        $left = $booleanOr->left;
        $ifs = $this->createMultipleIfs($left, $node, []);
        // ensure ifs not removed by other rules
        if ($ifs === []) {
            return null;
        }
        if (!$this->callAnalyzer->doesIfHasObjectCall($ifs)) {
            return null;
        }
        $this->mirrorComments($ifs[0], $node);
        $lastReturnExpr = $this->assignAndBinaryMap->getTruthyExpr($booleanOr->right);
        return \array_merge($ifs, [new \PhpParser\Node\Stmt\Return_($lastReturnExpr)]);
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(\PhpParser\Node\Expr $expr, \PhpParser\Node\Stmt\Return_ $return, array $ifs) : array
    {
        while ($expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            $ifs = \array_merge($ifs, $this->collectLeftBooleanOrToIfs($expr, $return, $ifs));
            $ifs[] = $this->ifManipulator->createIfExpr($expr->right, new \PhpParser\Node\Stmt\Return_($this->nodeFactory->createTrue()));
            $expr = $expr->right;
            if ($expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
                return [];
            }
            if (!$expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
                continue;
            }
            return [];
        }
        return $ifs + [$this->ifManipulator->createIfExpr($expr, new \PhpParser\Node\Stmt\Return_($this->nodeFactory->createTrue()))];
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanOrToIfs(\PhpParser\Node\Expr\BinaryOp\BooleanOr $BooleanOr, \PhpParser\Node\Stmt\Return_ $return, array $ifs) : array
    {
        $left = $BooleanOr->left;
        if (!$left instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return [$this->ifManipulator->createIfExpr($left, new \PhpParser\Node\Stmt\Return_($this->nodeFactory->createTrue()))];
        }
        return $this->createMultipleIfs($left, $return, $ifs);
    }
}
