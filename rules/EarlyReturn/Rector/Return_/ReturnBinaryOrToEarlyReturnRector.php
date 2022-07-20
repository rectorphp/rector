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
final class ReturnBinaryOrToEarlyReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    public function __construct(IfManipulator $ifManipulator, AssignAndBinaryMap $assignAndBinaryMap, CallAnalyzer $callAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->callAnalyzer = $callAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes Single return of || to early returns', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Return_::class];
    }
    /**
     * @param Return_ $node
     * @return null|Node[]
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof BooleanOr) {
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
        return \array_merge($ifs, [new Return_($lastReturnExpr)]);
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(Expr $expr, Return_ $return, array $ifs) : array
    {
        while ($expr instanceof BooleanOr) {
            $ifs = \array_merge($ifs, $this->collectLeftBooleanOrToIfs($expr, $return, $ifs));
            $ifs[] = $this->ifManipulator->createIfStmt($expr->right, new Return_($this->nodeFactory->createTrue()));
            $expr = $expr->right;
            if ($expr instanceof BooleanAnd) {
                return [];
            }
            if (!$expr instanceof BooleanOr) {
                continue;
            }
            return [];
        }
        return $ifs + [$this->ifManipulator->createIfStmt($expr, new Return_($this->nodeFactory->createTrue()))];
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanOrToIfs(BooleanOr $booleanOr, Return_ $return, array $ifs) : array
    {
        $left = $booleanOr->left;
        if (!$left instanceof BooleanOr) {
            return [$this->ifManipulator->createIfStmt($left, new Return_($this->nodeFactory->createTrue()))];
        }
        return $this->createMultipleIfs($left, $return, $ifs);
    }
}
