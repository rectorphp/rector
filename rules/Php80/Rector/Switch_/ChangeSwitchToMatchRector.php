<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Throw_;
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
 * @see https://3v4l.org/572T5
 *
 * @see \Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\ChangeSwitchToMatchRectorTest
 */
final class ChangeSwitchToMatchRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
    public function __construct(\Rector\Php80\NodeResolver\SwitchExprsResolver $switchExprsResolver, \Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer $matchSwitchAnalyzer, \Rector\Php80\NodeFactory\MatchFactory $matchFactory)
    {
        $this->switchExprsResolver = $switchExprsResolver;
        $this->matchSwitchAnalyzer = $matchSwitchAnalyzer;
        $this->matchFactory = $matchFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change switch() to match()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
            if ($condAndExpr->equalsMatchKind(\Rector\Php80\Enum\MatchKind::RETURN())) {
                $isReturn = \true;
                break;
            }
            $expr = $condAndExpr->getExpr();
            if ($expr instanceof \PhpParser\Node\Expr\Throw_) {
                continue;
            }
            if (!$expr instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
        }
        $match = $this->matchFactory->createFromCondAndExprs($node->cond, $condAndExprs);
        // implicit return default after switch
        $match = $this->processImplicitReturnAfterSwitch($node, $match, $condAndExprs);
        $match = $this->processImplicitThrowsAfterSwitch($node, $match, $condAndExprs);
        if ($isReturn) {
            return $this->processReturn($match);
        }
        $assignExpr = $this->resolveAssignExpr($condAndExprs);
        if ($assignExpr instanceof \PhpParser\Node\Expr) {
            return $this->changeToAssign($node, $match, $assignExpr);
        }
        return $match;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::MATCH_EXPRESSION;
    }
    private function processReturn(\PhpParser\Node\Expr\Match_ $match) : ?\PhpParser\Node\Stmt\Return_
    {
        if (!$this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return null;
        }
        return new \PhpParser\Node\Stmt\Return_($match);
    }
    private function changeToAssign(\PhpParser\Node\Stmt\Switch_ $switch, \PhpParser\Node\Expr\Match_ $match, \PhpParser\Node\Expr $assignExpr) : \PhpParser\Node\Expr\Assign
    {
        $prevInitializedAssign = $this->betterNodeFinder->findFirstPreviousOfNode($switch, function (\PhpParser\Node $node) use($assignExpr) : bool {
            return $node instanceof \PhpParser\Node\Expr\Assign && $this->nodeComparator->areNodesEqual($node->var, $assignExpr);
        }, \false);
        $assign = new \PhpParser\Node\Expr\Assign($assignExpr, $match);
        if (!$prevInitializedAssign instanceof \PhpParser\Node\Expr\Assign) {
            return $assign;
        }
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            $default = $match->arms[\count($match->arms) - 1]->body;
            if ($this->nodeComparator->areNodesEqual($default, $prevInitializedAssign->var)) {
                return $assign;
            }
        }
        $parentAssign = $prevInitializedAssign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentAssign instanceof \PhpParser\Node\Stmt\Expression) {
            $this->removeNode($parentAssign);
        }
        return $assign;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function resolveAssignExpr(array $condAndExprs) : ?\PhpParser\Node\Expr
    {
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (!$expr instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            return $expr->var;
        }
        return null;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitReturnAfterSwitch(\PhpParser\Node\Stmt\Switch_ $switch, \PhpParser\Node\Expr\Match_ $match, array $condAndExprs) : \PhpParser\Node\Expr\Match_
    {
        $nextNode = $switch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Return_) {
            return $match;
        }
        $returnedExpr = $nextNode->expr;
        if (!$returnedExpr instanceof \PhpParser\Node\Expr) {
            return $match;
        }
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return $match;
        }
        $assignExpr = $this->resolveAssignExpr($condAndExprs);
        if (!$assignExpr instanceof \PhpParser\Node\Expr) {
            $this->removeNode($nextNode);
        }
        $condAndExprs[] = new \Rector\Php80\ValueObject\CondAndExpr([], $returnedExpr, \Rector\Php80\Enum\MatchKind::RETURN());
        return $this->matchFactory->createFromCondAndExprs($switch->cond, $condAndExprs);
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitThrowsAfterSwitch(\PhpParser\Node\Stmt\Switch_ $switch, \PhpParser\Node\Expr\Match_ $match, array $condAndExprs) : \PhpParser\Node\Expr\Match_
    {
        $nextNode = $switch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Throw_) {
            return $match;
        }
        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return $match;
        }
        $this->removeNode($nextNode);
        $throw = new \PhpParser\Node\Expr\Throw_($nextNode->expr);
        $condAndExprs[] = new \Rector\Php80\ValueObject\CondAndExpr([], $throw, \Rector\Php80\Enum\MatchKind::RETURN());
        return $this->matchFactory->createFromCondAndExprs($switch->cond, $condAndExprs);
    }
}
