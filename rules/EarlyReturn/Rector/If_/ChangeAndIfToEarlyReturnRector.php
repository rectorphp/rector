<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\EarlyReturn\NodeAnalyzer\IfAndAnalyzer;
use Rector\EarlyReturn\NodeAnalyzer\SimpleScalarAnalyzer;
use Rector\EarlyReturn\NodeFactory\InvertedIfFactory;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeManipulator\IfManipulator;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Since 1.1.2, as this rule creates inverted conditions and makes code much less readable.
 */
final class ChangeAndIfToEarlyReturnRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeFactory\InvertedIfFactory
     */
    private $invertedIfFactory;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeCollector\BinaryOpConditionsCollector
     */
    private $binaryOpConditionsCollector;
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeAnalyzer\SimpleScalarAnalyzer
     */
    private $simpleScalarAnalyzer;
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeAnalyzer\IfAndAnalyzer
     */
    private $ifAndAnalyzer;
    public function __construct(IfManipulator $ifManipulator, InvertedIfFactory $invertedIfFactory, ContextAnalyzer $contextAnalyzer, BinaryOpConditionsCollector $binaryOpConditionsCollector, SimpleScalarAnalyzer $simpleScalarAnalyzer, IfAndAnalyzer $ifAndAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->invertedIfFactory = $invertedIfFactory;
        $this->contextAnalyzer = $contextAnalyzer;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
        $this->simpleScalarAnalyzer = $simpleScalarAnalyzer;
        $this->ifAndAnalyzer = $ifAndAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes if && to early return', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $car)
    {
        if (! $car->hasWheels) {
            return false;
        }

        if (! $car->hasFuel) {
            return false;
        }

        return true;
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
        return [ClassMethod::class, Function_::class, Foreach_::class, Closure::class, FileWithoutNamespace::class, Namespace_::class];
    }
    /**
     * @param Stmt\ClassMethod|Stmt\Function_|Stmt\Foreach_|Expr\Closure|FileWithoutNamespace|Stmt\Namespace_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stmts = (array) $node->stmts;
        if ($stmts === []) {
            return null;
        }
        $newStmts = [];
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                // keep natural original order
                $newStmts[] = $stmt;
                continue;
            }
            $nextStmt = $stmts[$key + 1] ?? null;
            if ($this->isComplexReturn($nextStmt)) {
                return null;
            }
            if ($this->shouldSkip($stmt, $nextStmt)) {
                $newStmts[] = $stmt;
                continue;
            }
            if ($nextStmt instanceof Return_) {
                if ($this->ifAndAnalyzer->isIfStmtExprUsedInNextReturn($stmt, $nextStmt)) {
                    continue;
                }
                if ($nextStmt->expr instanceof BooleanAnd) {
                    continue;
                }
            }
            /** @var BooleanAnd $expr */
            $expr = $stmt->cond;
            $booleanAndConditions = $this->binaryOpConditionsCollector->findConditions($expr, BooleanAnd::class);
            $afterStmts = [];
            if (!$nextStmt instanceof Return_) {
                $afterStmts[] = $stmt->stmts[0];
                $node->stmts = \array_merge($newStmts, $this->processReplaceIfs($stmt, $booleanAndConditions, new Return_(), $afterStmts, $nextStmt));
                return $node;
            }
            // remove next node
            unset($newStmts[$key + 1]);
            $afterStmts[] = $stmt->stmts[0];
            $ifNextReturnClone = $stmt->stmts[0] instanceof Return_ ? clone $stmt->stmts[0] : new Return_();
            if ($this->isInLoopWithoutContinueOrBreak($stmt)) {
                $afterStmts[] = new Return_();
            }
            $changedStmts = $this->processReplaceIfs($stmt, $booleanAndConditions, $ifNextReturnClone, $afterStmts, $nextStmt);
            // update stmts
            $node->stmts = \array_merge($newStmts, $changedStmts);
            return $node;
        }
        return null;
    }
    private function isInLoopWithoutContinueOrBreak(If_ $if) : bool
    {
        if (!$this->contextAnalyzer->isInLoop($if)) {
            return \false;
        }
        if ($if->stmts[0] instanceof Continue_) {
            return \false;
        }
        return !$if->stmts[0] instanceof Break_;
    }
    /**
     * @param Expr[] $conditions
     * @param Stmt[] $afters
     * @return Stmt[]
     */
    private function processReplaceIfs(If_ $if, array $conditions, Return_ $ifNextReturn, array $afters, ?Stmt $nextStmt) : array
    {
        $ifs = $this->invertedIfFactory->createFromConditions($if, $conditions, $ifNextReturn, $nextStmt);
        $this->mirrorComments($ifs[0], $if);
        $result = \array_merge($ifs, $afters);
        if ($if->stmts[0] instanceof Return_) {
            return $result;
        }
        if (!$ifNextReturn->expr instanceof Expr) {
            return $result;
        }
        if ($this->contextAnalyzer->isInLoop($if)) {
            return $result;
        }
        return \array_merge($result, [$ifNextReturn]);
    }
    private function shouldSkip(If_ $if, ?Stmt $nexStmt) : bool
    {
        if (!$this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return \true;
        }
        if (!$if->cond instanceof BooleanAnd) {
            return \true;
        }
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($if)) {
            return \true;
        }
        // is simple return? skip it
        $onlyStmt = $if->stmts[0];
        if ($onlyStmt instanceof Return_ && $onlyStmt->expr instanceof Expr && $this->simpleScalarAnalyzer->isSimpleScalar($onlyStmt->expr)) {
            return \true;
        }
        if ($this->ifAndAnalyzer->isIfAndWithInstanceof($if->cond)) {
            return \true;
        }
        return !$this->isLastIfOrBeforeLastReturn($nexStmt);
    }
    private function isLastIfOrBeforeLastReturn(?Stmt $nextStmt) : bool
    {
        if (!$nextStmt instanceof Stmt) {
            return \true;
        }
        return $nextStmt instanceof Return_;
    }
    private function isComplexReturn(?Stmt $stmt) : bool
    {
        if (!$stmt instanceof Return_) {
            return \false;
        }
        if (!$stmt->expr instanceof Expr) {
            return \false;
        }
        if ($stmt->expr instanceof ConstFetch) {
            return \false;
        }
        return !$stmt->expr instanceof Scalar;
    }
}
