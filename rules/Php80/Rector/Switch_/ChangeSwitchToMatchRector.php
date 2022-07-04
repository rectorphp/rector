<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_ as ThrowsStmt;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\Enum\MatchKind;
use Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Rector\Php80\NodeFactory\MatchFactory;
use Rector\Php80\NodeResolver\SwitchExprsResolver;
use Rector\Php80\ValueObject\CondAndExpr;
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
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $condAndExprs = $this->switchExprsResolver->resolve($node);
        if ($this->matchSwitchAnalyzer->shouldSkipSwitch($node, $condAndExprs)) {
            return null;
        }
        if (!$this->matchSwitchAnalyzer->haveCondAndExprsMatchPotential($condAndExprs)) {
            return null;
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
                return null;
            }
        }
        $match = $this->matchFactory->createFromCondAndExprs($node->cond, $condAndExprs);
        // implicit return default after switch
        $match = $this->processImplicitReturnAfterSwitch($node, $match, $condAndExprs);
        $match = $this->processImplicitThrowsAfterSwitch($node, $match, $condAndExprs);
        $assignVar = $this->resolveAssignVar($condAndExprs);
        $hasDefaultValue = $this->matchSwitchAnalyzer->hasDefaultValue($match);
        if ($assignVar instanceof Expr) {
            return $this->changeToAssign($node, $match, $assignVar, $hasDefaultValue);
        }
        if (!$hasDefaultValue) {
            return null;
        }
        return $isReturn ? new Return_($match) : $match;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::MATCH_EXPRESSION;
    }
    private function changeToAssign(Switch_ $switch, Match_ $match, Expr $expr, bool $hasDefaultValue) : ?Assign
    {
        $nextReturn = $switch->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextReturn instanceof Return_ && $nextReturn->expr instanceof Expr && !$this->nodeComparator->areNodesEqual($expr, $nextReturn->expr)) {
            return null;
        }
        $prevInitializedAssign = $this->betterNodeFinder->findFirstInlinedPrevious($switch, function (Node $node) use($expr) : bool {
            return $node instanceof Assign && $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
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
            $match->arms[\count($match->arms)] = new MatchArm(null, $prevInitializedAssign->expr);
        }
        $parentAssign = $prevInitializedAssign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentAssign instanceof Expression) {
            $this->removeNode($parentAssign);
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
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitReturnAfterSwitch(Switch_ $switch, Match_ $match, array $condAndExprs) : Match_
    {
        $nextNode = $switch->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof Return_) {
            return $match;
        }
        $returnedExpr = $nextNode->expr;
        if (!$returnedExpr instanceof Expr) {
            return $match;
        }
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return $match;
        }
        $assignVar = $this->resolveAssignVar($condAndExprs);
        if (!$assignVar instanceof Expr) {
            $this->removeNode($nextNode);
        }
        $condAndExprs[] = new CondAndExpr([], $returnedExpr, MatchKind::RETURN);
        return $this->matchFactory->createFromCondAndExprs($switch->cond, $condAndExprs);
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitThrowsAfterSwitch(Switch_ $switch, Match_ $match, array $condAndExprs) : Match_
    {
        $nextNode = $switch->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof ThrowsStmt) {
            return $match;
        }
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return $match;
        }
        $this->removeNode($nextNode);
        $throw = new Throw_($nextNode->expr);
        $condAndExprs[] = new CondAndExpr([], $throw, MatchKind::RETURN);
        return $this->matchFactory->createFromCondAndExprs($switch->cond, $condAndExprs);
    }
}
