<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Rector\Php80\NodeFactory\MatchFactory;
use Rector\Php80\NodeResolver\SwitchExprsResolver;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\Php80\ValueObject\MatchResult;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
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
            $isReturn = $this->matchSwitchAnalyzer->isReturnCondsAndExprs($condAndExprs);
            if ($this->nodeTypeResolver->getType($stmt->cond) instanceof ObjectType) {
                continue;
            }
            $matchResult = $this->matchFactory->createFromCondAndExprs($stmt->cond, $condAndExprs, $nextStmt);
            if (!$matchResult instanceof MatchResult) {
                continue;
            }
            $match = $matchResult->getMatch();
            if ($matchResult->shouldRemoveNextStmt() && $isReturn) {
                unset($node->stmts[$key + 1]);
            }
            $assignVar = $this->resolveAssignVar($condAndExprs);
            $hasDefaultValue = $this->matchSwitchAnalyzer->hasDefaultValue($match);
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
            $node->stmts[$key] = $isReturn ? new Return_($match) : new Expression($match);
            $this->mirrorComments($node->stmts[$key], $stmt);
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
}
