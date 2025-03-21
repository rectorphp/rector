<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeManipulator\IfManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\ConsecutiveNullCompareReturnsToNullCoalesceQueueRectorTest
 */
final class ConsecutiveNullCompareReturnsToNullCoalesceQueueRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private IfManipulator $ifManipulator;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(IfManipulator $ifManipulator, ValueResolver $valueResolver)
    {
        $this->ifManipulator = $ifManipulator;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change multiple null compares to ?? queue', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($this->orderItem !== null) {
            return $this->orderItem;
        }

        if ($this->orderItemUnit !== null) {
            return $this->orderItemUnit;
        }

        return null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->orderItem ?? $this->orderItemUnit;
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
        $coalescingExprs = [];
        $ifKeys = [];
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $comparedExpr = $this->ifManipulator->matchIfNotNullReturnValue($stmt);
            if (!$comparedExpr instanceof Expr) {
                continue;
            }
            if (!isset($node->stmts[$key + 1])) {
                return null;
            }
            $coalescingExprs[] = $comparedExpr;
            $ifKeys[] = $key;
        }
        // at least 2 coalescing nodes are needed
        if (\count($coalescingExprs) < 2) {
            return null;
        }
        // remove last return null
        $appendExpr = null;
        $hasChanged = \false;
        $originalStmts = $node->stmts;
        foreach ($node->stmts as $key => $stmt) {
            if (\in_array($key, $ifKeys, \true)) {
                unset($node->stmts[$key]);
                $hasChanged = \true;
                continue;
            }
            if (!$hasChanged) {
                continue;
            }
            if ($stmt instanceof Expression && $stmt->expr instanceof Throw_) {
                unset($node->stmts[$key]);
                $appendExpr = $stmt->expr;
                continue;
            }
            if (!$this->isReturnNull($stmt)) {
                if ($stmt instanceof Return_ && $stmt->expr instanceof Expr) {
                    unset($node->stmts[$key]);
                    $appendExpr = $stmt->expr;
                    continue;
                }
                $node->stmts = $originalStmts;
                return null;
            }
            unset($node->stmts[$key]);
        }
        $node->stmts[] = $this->createCealesceReturn($coalescingExprs, $appendExpr);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    private function isReturnNull(Stmt $stmt) : bool
    {
        if (!$stmt instanceof Return_) {
            return \false;
        }
        if (!$stmt->expr instanceof Expr) {
            return \false;
        }
        return $this->valueResolver->isNull($stmt->expr);
    }
    /**
     * @param Expr[] $coalescingExprs
     */
    private function createCealesceReturn(array $coalescingExprs, ?Expr $appendExpr) : Return_
    {
        /** @var Expr $leftExpr */
        $leftExpr = \array_shift($coalescingExprs);
        /** @var Expr $rightExpr */
        $rightExpr = \array_shift($coalescingExprs);
        $coalesce = new Coalesce($leftExpr, $rightExpr);
        foreach ($coalescingExprs as $coalescingExpr) {
            $coalesce = new Coalesce($coalesce, $coalescingExpr);
        }
        if ($appendExpr instanceof Expr) {
            return new Return_(new Coalesce($coalesce, $appendExpr));
        }
        return new Return_($coalesce);
    }
}
