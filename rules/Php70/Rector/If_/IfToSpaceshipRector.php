<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php70\Enum\BattleshipCompareOrder;
use Rector\Php70\NodeAnalyzer\BattleshipTernaryAnalyzer;
use Rector\Php70\ValueObject\ComparedExprs;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/combined-comparison-operator https://3v4l.org/LPbA0
 *
 * @see \Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\IfToSpaceshipRectorTest
 */
final class IfToSpaceshipRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php70\NodeAnalyzer\BattleshipTernaryAnalyzer
     */
    private $battleshipTernaryAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(BattleshipTernaryAnalyzer $battleshipTernaryAnalyzer, ValueResolver $valueResolver)
    {
        $this->battleshipTernaryAnalyzer = $battleshipTernaryAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes if/else to spaceship <=> where useful', [new CodeSample(<<<'CODE_SAMPLE'
usort($languages, function ($first, $second) {
if ($first[0] === $second[0]) {
    return 0;
}

return ($first[0] < $second[0]) ? 1 : -1;
});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
usort($languages, function ($first, $second) {
return $second[0] <=> $first[0];
});
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class, If_::class];
    }
    /**
     * @param StmtsAwareInterface|If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof If_) {
            return $this->refactorIf($node);
        }
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Ternary) {
                continue;
            }
            // preceeded by if
            $prevStmt = $node->stmts[$key - 1] ?? null;
            if (!$prevStmt instanceof If_) {
                continue;
            }
            $comparedExprs = $this->matchExprComparedExprsReturnZero($prevStmt);
            if (!$comparedExprs instanceof ComparedExprs) {
                continue;
            }
            $battleshipCompareOrder = $this->battleshipTernaryAnalyzer->isGreaterLowerCompareReturnOneAndMinusOne($stmt->expr, $comparedExprs);
            $returnSpaceship = $this->createReturnSpaceship($battleshipCompareOrder, $comparedExprs);
            if (!$returnSpaceship instanceof Return_) {
                continue;
            }
            unset($node->stmts[$key - 1]);
            $node->stmts[$key] = $returnSpaceship;
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SPACESHIP;
    }
    private function refactorIf(If_ $if) : ?Return_
    {
        if ($if->elseifs !== []) {
            return null;
        }
        if (!$if->else instanceof Else_) {
            return null;
        }
        $comparedExprs = $this->matchExprComparedExprsReturnZero($if);
        if (!$comparedExprs instanceof ComparedExprs) {
            return null;
        }
        $ternary = $this->matchElseOnlyStmtTernary($if->else);
        if (!$ternary instanceof Ternary) {
            return null;
        }
        $battleshipCompareOrder = $this->battleshipTernaryAnalyzer->isGreaterLowerCompareReturnOneAndMinusOne($ternary, $comparedExprs);
        return $this->createReturnSpaceship($battleshipCompareOrder, $comparedExprs);
    }
    /**
     * We look for:
     *
     * if ($firstValue === $secondValue) {
     *      return 0;
     * }
     */
    private function matchExprComparedExprsReturnZero(If_ $if) : ?ComparedExprs
    {
        if (!$if->cond instanceof Equal && !$if->cond instanceof Identical) {
            return null;
        }
        $binaryOp = $if->cond;
        if (\count($if->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $if->stmts[0];
        if (!$onlyStmt instanceof Return_) {
            return null;
        }
        if (!$onlyStmt->expr instanceof Expr) {
            return null;
        }
        if (!$this->valueResolver->isValue($onlyStmt->expr, 0)) {
            return null;
        }
        return new ComparedExprs($binaryOp->left, $binaryOp->right);
    }
    /**
     * @param BattleshipCompareOrder::*|null $battleshipCompareOrder
     */
    private function createReturnSpaceship(?string $battleshipCompareOrder, ComparedExprs $comparedExprs) : ?Return_
    {
        if ($battleshipCompareOrder === null) {
            return null;
        }
        if ($battleshipCompareOrder === BattleshipCompareOrder::DESC) {
            $spaceship = new Spaceship($comparedExprs->getFirstExpr(), $comparedExprs->getSecondExpr());
        } else {
            $spaceship = new Spaceship($comparedExprs->getSecondExpr(), $comparedExprs->getFirstExpr());
        }
        return new Return_($spaceship);
    }
    private function matchElseOnlyStmtTernary(Else_ $else) : ?\PhpParser\Node\Expr\Ternary
    {
        if (\count($else->stmts) !== 1) {
            return null;
        }
        $onlyElseStmt = $else->stmts[0];
        if (!$onlyElseStmt instanceof Return_) {
            return null;
        }
        if (!$onlyElseStmt->expr instanceof Ternary) {
            return null;
        }
        return $onlyElseStmt->expr;
    }
}
