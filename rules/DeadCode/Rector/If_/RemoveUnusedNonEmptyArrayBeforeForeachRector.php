<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\DeadCode\NodeManipulator\CountManipulator;
use Rector\DeadCode\UselessIfCondBeforeForeachDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\RemoveUnusedNonEmptyArrayBeforeForeachRectorTest
 */
final class RemoveUnusedNonEmptyArrayBeforeForeachRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\CountManipulator
     */
    private $countManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\DeadCode\UselessIfCondBeforeForeachDetector
     */
    private $uselessIfCondBeforeForeachDetector;
    /**
     * @readonly
     * @var \Rector\Core\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(CountManipulator $countManipulator, IfManipulator $ifManipulator, UselessIfCondBeforeForeachDetector $uselessIfCondBeforeForeachDetector, ReservedKeywordAnalyzer $reservedKeywordAnalyzer, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->countManipulator = $countManipulator;
        $this->ifManipulator = $ifManipulator;
        $this->uselessIfCondBeforeForeachDetector = $uselessIfCondBeforeForeachDetector;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused if check to non-empty array before foreach of the array', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }
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
        return [If_::class, StmtsAwareInterface::class];
    }
    /**
     * @param If_|StmtsAwareInterface $node
     * @return Stmt[]|Foreach_|StmtsAwareInterface|null
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        if ($node instanceof If_) {
            return $this->refactorIf($node, $scope);
        }
        return $this->refactorStmtsAware($node);
    }
    private function isUselessBeforeForeachCheck(If_ $if, Scope $scope) : bool
    {
        if (!$this->ifManipulator->isIfWithOnly($if, Foreach_::class)) {
            return \false;
        }
        /** @var Foreach_ $foreach */
        $foreach = $if->stmts[0];
        $foreachExpr = $foreach->expr;
        if ($foreachExpr instanceof Variable) {
            $variableName = $this->nodeNameResolver->getName($foreachExpr);
            if (\is_string($variableName) && $this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
                return \false;
            }
        }
        $ifCond = $if->cond;
        if ($ifCond instanceof BooleanAnd) {
            return $this->isUselessBooleanAnd($ifCond, $foreachExpr);
        }
        if (($ifCond instanceof Variable || $this->propertyFetchAnalyzer->isPropertyFetch($ifCond)) && $this->nodeComparator->areNodesEqual($ifCond, $foreachExpr)) {
            $ifType = $scope->getType($ifCond);
            return $ifType->isArray()->yes();
        }
        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotIdenticalEmptyArray($if, $foreachExpr)) {
            return \true;
        }
        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotEmpty($if, $foreachExpr, $scope)) {
            return \true;
        }
        return $this->countManipulator->isCounterHigherThanOne($if->cond, $foreachExpr);
    }
    private function isUselessBooleanAnd(BooleanAnd $booleanAnd, Expr $foreachExpr) : bool
    {
        if (!$booleanAnd->left instanceof Variable) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($booleanAnd->left, $foreachExpr)) {
            return \false;
        }
        return $this->countManipulator->isCounterHigherThanOne($booleanAnd->right, $foreachExpr);
    }
    private function refactorStmtsAware(StmtsAwareInterface $stmtsAware) : ?StmtsAwareInterface
    {
        if ($stmtsAware->stmts === null) {
            return null;
        }
        foreach ($stmtsAware->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            $previousStmt = $stmtsAware->stmts[$key - 1] ?? null;
            if (!$previousStmt instanceof If_) {
                continue;
            }
            // not followed by any stmts
            $nextStmt = $stmtsAware->stmts[$key + 1] ?? null;
            if ($nextStmt instanceof Stmt) {
                continue;
            }
            if (!$this->uselessIfCondBeforeForeachDetector->isMatchingEmptyAndForeachedExpr($previousStmt, $stmt->expr)) {
                continue;
            }
            unset($stmtsAware->stmts[$key - 1]);
            return $stmtsAware;
        }
        return null;
    }
    private function refactorIf(If_ $if, Scope $scope) : ?Foreach_
    {
        if (!$this->isUselessBeforeForeachCheck($if, $scope)) {
            return null;
        }
        /** @var Foreach_ $stmt */
        $stmt = $if->stmts[0];
        $ifComments = $if->getAttribute(AttributeKey::COMMENTS) ?? [];
        $stmtComments = $stmt->getAttribute(AttributeKey::COMMENTS) ?? [];
        $comments = \array_merge($ifComments, $stmtComments);
        $stmt->setAttribute(AttributeKey::COMMENTS, $comments);
        return $stmt;
    }
}
