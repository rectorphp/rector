<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\PhpParser\Node\AssignAndBinaryMap;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\ReturnBinaryOrToEarlyReturnRectorTest
 */
final class ReturnBinaryOrToEarlyReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, CallAnalyzer $callAnalyzer)
    {
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
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof BooleanOr) {
                continue;
            }
            /** @var BooleanOr $booleanOr */
            $booleanOr = $stmt->expr;
            $left = $booleanOr->left;
            $ifs = $this->createMultipleIfs($left, $stmt, []);
            // ensure ifs not removed by other rules
            if ($ifs === []) {
                continue;
            }
            if (!$this->callAnalyzer->doesIfHasObjectCall($ifs)) {
                continue;
            }
            $this->mirrorComments($ifs[0], $stmt);
            $lastReturnExpr = $this->assignAndBinaryMap->getTruthyExpr($booleanOr->right);
            $ifsWithLastIf = \array_merge($ifs, [new Return_($lastReturnExpr)]);
            \array_splice($node->stmts, $key, 1, $ifsWithLastIf);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(Expr $expr, Return_ $return, array $ifs) : array
    {
        while ($expr instanceof BooleanOr) {
            $ifs = \array_merge($ifs, $this->collectLeftBooleanOrToIfs($expr, $return, $ifs));
            $ifs[] = new If_($expr->right, ['stmts' => [new Return_($this->nodeFactory->createTrue())]]);
            $expr = $expr->right;
            if ($expr instanceof BooleanAnd) {
                return [];
            }
            if (!$expr instanceof BooleanOr) {
                continue;
            }
            return [];
        }
        $lastIf = new If_($expr, ['stmts' => [new Return_($this->nodeFactory->createTrue())]]);
        // if empty, fallback to last if
        if ($ifs === []) {
            return [$lastIf];
        }
        return $ifs;
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanOrToIfs(BooleanOr $booleanOr, Return_ $return, array $ifs) : array
    {
        $left = $booleanOr->left;
        if (!$left instanceof BooleanOr) {
            $returnTrueIf = new If_($left, ['stmts' => [new Return_($this->nodeFactory->createTrue())]]);
            return [$returnTrueIf];
        }
        return $this->createMultipleIfs($left, $return, $ifs);
    }
}
