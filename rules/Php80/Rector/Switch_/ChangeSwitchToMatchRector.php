<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\Enum\MatchKind;
use Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Rector\Php80\NodeFactory\MatchFactory;
use Rector\Php80\NodeResolver\SwitchExprsResolver;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\Php80\ValueObject\MatchResult;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/match_expression_v2
 * @changelog https://3v4l.org/572T5
 *
 * @see \Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\ChangeSwitchToMatchRectorTest
 */
final class ChangeSwitchToMatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeResolver\SwitchExprsResolver
     */
    private $switchExprsResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer
     */
    private $matchSwitchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\MatchFactory
     */
    private $matchFactory;
    public function __construct(SwitchExprsResolver $switchExprsResolver, MatchSwitchAnalyzer $matchSwitchAnalyzer, MatchFactory $matchFactory)
    {
        $this->switchExprsResolver = $switchExprsResolver;
        $this->matchSwitchAnalyzer = $matchSwitchAnalyzer;
        $this->matchFactory = $matchFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change switch() to match()', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!\is_array($node->stmts)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Switch_) {
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
            $isReturn = \false;
            foreach ($condAndExprs as $condAndExpr) {
                if ($condAndExpr->equalsMatchKind(MatchKind::RETURN)) {
                    $isReturn = \true;
                    break;
                }
                $expr = $condAndExpr->getExpr();
                if ($expr instanceof Throw_) {
                    continue;
                }
                if (!$expr instanceof Assign) {
                    continue 2;
                }
            }
            $matchResult = $this->matchFactory->createFromCondAndExprs($stmt->cond, $condAndExprs, $nextStmt);
            if (!$matchResult instanceof MatchResult) {
                continue;
            }
            $match = $matchResult->getMatch();
            if ($matchResult->shouldRemoveNextStmt()) {
                unset($node->stmts[$key + 1]);
            }
            $assignVar = $this->resolveAssignVar($condAndExprs);
            $hasDefaultValue = $this->matchSwitchAnalyzer->hasDefaultValue($match);
            if ($assignVar instanceof Expr) {
                $previousStmt = $node->stmts[$key - 1] ?? null;
                $assign = $this->changeToAssign($match, $assignVar, $hasDefaultValue, $previousStmt, $nextStmt);
                if (!$assign instanceof Assign) {
                    continue;
                }
                $node->stmts[$key] = new Expression($assign);
                $hasChanged = \true;
                continue;
            }
            if (!$hasDefaultValue) {
                continue;
            }
            $node->stmts[$key] = $isReturn ? new Return_($match) : new Expression($match);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::MATCH_EXPRESSION;
    }
    private function changeToAssign(Match_ $match, Expr $expr, bool $hasDefaultValue, ?Stmt $previousStmt, ?Stmt $nextStmt) : ?Assign
    {
        // containts next this expr?
        if (!$hasDefaultValue && $this->isFollowedByReturnWithExprUsage($nextStmt, $expr)) {
            return null;
        }
        // @todo extract?
        $prevInitializedAssign = null;
        if ($previousStmt instanceof Expression) {
            $previousExpr = $previousStmt->expr;
            if ($previousExpr instanceof Assign && $this->nodeComparator->areNodesEqual($previousExpr->var, $expr)) {
                $prevInitializedAssign = $previousExpr;
            }
        }
        $assign = new Assign($expr, $match);
        if (!$prevInitializedAssign instanceof Assign) {
            return $this->resolveCurrentAssign($hasDefaultValue, $assign);
        }
        if ($hasDefaultValue) {
            $default = $match->arms[\count($match->arms) - 1]->body;
            if ($this->nodeComparator->areNodesEqual($default, $prevInitializedAssign->var)) {
                return $assign;
            }
        } else {
            $lastArmPosition = \count($match->arms);
            $match->arms[$lastArmPosition] = new MatchArm(null, $prevInitializedAssign->expr);
        }
        $node = $prevInitializedAssign->getAttribute(AttributeKey::PARENT_NODE);
        if ($node instanceof Expression) {
            $this->removeNode($node);
        }
        return $assign;
    }
    private function resolveCurrentAssign(bool $hasDefaultValue, Assign $assign) : ?Assign
    {
        return $hasDefaultValue ? $assign : null;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function resolveAssignVar(array $condAndExprs) : ?Expr
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
    private function isFollowedByReturnWithExprUsage(?\PhpParser\Node\Stmt $nextStmt, Expr $expr) : bool
    {
        if (!$nextStmt instanceof Return_) {
            return \false;
        }
        if (!$nextStmt->expr instanceof Expr) {
            return \false;
        }
        $returnExprs = $this->betterNodeFinder->findInstanceOf($nextStmt, Expr::class);
        foreach ($returnExprs as $returnExpr) {
            if ($this->nodeComparator->areNodesEqual($expr, $returnExpr)) {
                return \true;
            }
        }
        return \false;
    }
}
