<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Type\ObjectType;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Rector\Php80\NodeFactory\MatchFactory;
use Rector\Php80\NodeResolver\SwitchExprsResolver;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\Php80\ValueObject\MatchResult;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\ChangeSwitchToMatchRectorTest
 */
final class ChangeSwitchToMatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private SwitchExprsResolver $switchExprsResolver;
    /**
     * @readonly
     */
    private MatchSwitchAnalyzer $matchSwitchAnalyzer;
    /**
     * @readonly
     */
    private MatchFactory $matchFactory;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(SwitchExprsResolver $switchExprsResolver, MatchSwitchAnalyzer $matchSwitchAnalyzer, MatchFactory $matchFactory, ValueResolver $valueResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->switchExprsResolver = $switchExprsResolver;
        $this->matchSwitchAnalyzer = $matchSwitchAnalyzer;
        $this->matchFactory = $matchFactory;
        $this->valueResolver = $valueResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `switch()` to `match()`', [new CodeSample(<<<'CODE_SAMPLE'
switch ($input) {
    case Lexer::T_SELECT:
        $statement = 'select';
        break;
    case Lexer::T_UPDATE:
        $statement = 'update';
        break;
    default:
        $statement = 'error';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$statement = match ($input) {
    Lexer::T_SELECT => 'select',
    Lexer::T_UPDATE => 'update',
    default => 'error',
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?\PhpParser\Node
    {
        if (!is_array($node->stmts)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Switch_) {
                continue;
            }
            // possible reference override, where match
            if ($stmt->getAttribute(AttributeKey::IS_INSIDE_BYREF_FUNCTION_LIKE)) {
                continue;
            }
            $nextStmt = $node->stmts[$key + 1] ?? null;
            $condAndExprs = $this->switchExprsResolver->resolve($stmt);
            if ($this->matchSwitchAnalyzer->shouldSkipSwitch($stmt, $condAndExprs, $nextStmt)) {
                continue;
            }
            if (!$this->matchSwitchAnalyzer->haveCondAndExprsMatchPotential($condAndExprs)) {
                continue;
            }
            $isReturn = $this->matchSwitchAnalyzer->isReturnCondsAndExprs($condAndExprs);
            if ($this->getType($stmt->cond) instanceof ObjectType) {
                continue;
            }
            $matchResult = $this->matchFactory->createFromCondAndExprs($stmt->cond, $condAndExprs, $nextStmt);
            if (!$matchResult instanceof MatchResult) {
                continue;
            }
            $match = $matchResult->getMatch();
            // switch compares loosely, match strictly
            if ($this->isLooseComparisonChanged($match)) {
                continue;
            }
            if ($matchResult->shouldRemoveNextStmt() && $isReturn) {
                unset($node->stmts[$key + 1]);
            }
            $assignVar = $this->resolveAssignVar($condAndExprs);
            $hasDefaultValue = $this->matchSwitchAnalyzer->hasDefaultValue($match);
            $this->castMatchCond($match);
            $this->mirrorDynamicBoolExpr($match);
            if ($assignVar instanceof Expr) {
                if (!$hasDefaultValue) {
                    continue;
                }
                $assign = new Assign($assignVar, $match);
                $node->stmts[$key] = new Expression($assign);
                $this->mirrorComments($node->stmts[$key], $stmt);
                $hasChanged = \true;
                continue;
            }
            if (!$hasDefaultValue) {
                continue;
            }
            foreach ($match->arms as $arm) {
                if ($arm->conds === null) {
                    continue;
                }
                $arm->conds = array_values($arm->conds);
            }
            $node->stmts[$key] = $isReturn ? new Return_($match) : new Expression($match);
            $this->mirrorComments($node->stmts[$key], $stmt);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_80;
    }
    private function castMatchCond(Match_ $match): void
    {
        if (!$this->nodeTypeResolver->getNativeType($match->cond)->isInteger()->yes()) {
            return;
        }
        // arms are either all strings or all non-strings, see isLooseComparisonChanged()
        foreach ($match->arms as $arm) {
            if ($arm->conds === null) {
                continue;
            }
            if ($this->nodeTypeResolver->getNativeType($arm->conds[0])->isString()->yes()) {
                $match->cond = new String_($match->cond);
            }
            return;
        }
    }
    private function isLooseComparisonChanged(Match_ $match): bool
    {
        $condType = $this->nodeTypeResolver->getNativeType($match->cond);
        $stringArmTypes = [];
        $hasNonStringArm = \false;
        $hasIntArm = \false;
        foreach ($match->arms as $arm) {
            if ($arm->conds === null) {
                continue;
            }
            foreach ($arm->conds as $armCond) {
                $armCondType = $this->nodeTypeResolver->getNativeType($armCond);
                if ($armCondType->isString()->yes()) {
                    $stringArmTypes[] = $armCondType;
                    continue;
                }
                $hasNonStringArm = \true;
                if ($armCondType->isInteger()->yes()) {
                    $hasIntArm = \true;
                }
            }
        }
        // (int) cast would turn loose "777abc" == 777 (false) into 777 === 777 (true)
        if ($condType->isString()->yes()) {
            return $hasIntArm;
        }
        if ($stringArmTypes === []) {
            return \false;
        }
        // unknown subject: true == 'a' or 1 == '1' would stop matching
        if (!$condType->isInteger()->yes()) {
            return \true;
        }
        // one (string) cast cannot fit both string and non-string arms
        if ($hasNonStringArm) {
            return \true;
        }
        // (string) cast mirrors loose == only for non-numeric or canonical int strings, 1 == '01' holds but '1' === '01' does not
        foreach ($stringArmTypes as $stringArmType) {
            $constantStringTypes = $stringArmType->getConstantStrings();
            if ($constantStringTypes === []) {
                return \true;
            }
            foreach ($constantStringTypes as $constantStringType) {
                $value = $constantStringType->getValue();
                if (is_numeric($value) && (string) (int) $value !== $value) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function mirrorDynamicBoolExpr(Match_ $match): void
    {
        // switch(true) already just use
        // switch(false) is dead code that can be on purpose
        if ($this->valueResolver->isTrueOrFalse($match->cond)) {
            return;
        }
        $isChanged = \false;
        foreach ($match->arms as $arm) {
            if ($arm->conds === null) {
                continue;
            }
            foreach ($arm->conds as $cond) {
                if ($this->exprAnalyzer->isBoolExpr($cond) || $this->exprAnalyzer->isCallLikeReturnNativeBool($cond)) {
                    // dont' stop lookup for dynamic conditions
                    // continue verify other condition, in case of mixed condition
                    $isChanged = \true;
                    continue;
                }
                // return early here, as condition is mixed
                // we need another real use case for mixed conditions of dynamic + non-dynamic case expr
                return;
            }
        }
        if ($isChanged) {
            $match->cond = $this->nodeFactory->createTrue();
        }
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function resolveAssignVar(array $condAndExprs): ?Expr
    {
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (!$expr instanceof Assign) {
                continue;
            }
            return $expr->var;
        }
        return null;
    }
}
