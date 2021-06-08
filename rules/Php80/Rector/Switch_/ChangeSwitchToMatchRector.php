<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Rector\Php80\NodeFactory\MatchFactory;
use Rector\Php80\NodeResolver\SwitchExprsResolver;
use Rector\Php80\ValueObject\CondAndExpr;
use Rector\Php80\ValueObject\MatchKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/match_expression_v2
 * @see https://3v4l.org/572T5
 *
 * @see \Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\ChangeSwitchToMatchRectorTest
 */
final class ChangeSwitchToMatchRector extends AbstractRector
{
    public function __construct(
        private SwitchExprsResolver $switchExprsResolver,
        private MatchSwitchAnalyzer $matchSwitchAnalyzer,
        private MatchFactory $matchFactory,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change switch() to match()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_SELECT:
                $statement = $this->SelectStatement();
                break;

            case Lexer::T_UPDATE:
                $statement = $this->UpdateStatement();
                break;

            default:
                $statement = $this->syntaxError('SELECT, UPDATE or DELETE');
                break;
        }
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $statement = match ($this->lexer->lookahead['type']) {
            Lexer::T_SELECT => $this->SelectStatement(),
            Lexer::T_UPDATE => $this->UpdateStatement(),
            default => $this->syntaxError('SELECT, UPDATE or DELETE'),
        };
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }

    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $condAndExprs = $this->switchExprsResolver->resolve($node);
        if ($this->matchSwitchAnalyzer->shouldSkipSwitch($node, $condAndExprs)) {
            return null;
        }

        if (! $this->matchSwitchAnalyzer->haveCondAndExprsMatchPotential($condAndExprs)) {
            return null;
        }

        $isReturn = false;

        foreach ($condAndExprs as $condAndExpr) {
            if ($condAndExpr->getKind() === MatchKind::RETURN) {
                $isReturn = true;
                break;
            }

            $expr = $condAndExpr->getExpr();
            if ($expr instanceof Throw_) {
                continue;
            }

            if (! $expr instanceof Assign) {
                return null;
            }
        }

        $match = $this->matchFactory->createFromCondAndExprs($node->cond, $condAndExprs);

        // implicit return default after switch
        $match = $this->processImplicitReturnAfterSwitch($node, $match, $condAndExprs);

        if ($isReturn) {
            return new Return_($match);
        }

        $assignExpr = $this->resolveAssignExpr($condAndExprs);
        if ($assignExpr instanceof Expr) {
            return $this->changeToAssign($node, $match, $assignExpr);
        }

        return $match;
    }

    private function changeToAssign(Switch_ $switch, Match_ $match, Expr $assignExpr): Assign
    {
        $prevInitializedAssign = $this->betterNodeFinder->findFirstPreviousOfNode(
            $switch,
            fn (Node $node): bool => $node instanceof Assign && $this->nodeComparator->areNodesEqual(
                $node->var,
                $assignExpr
            )
        );

        $assign = new Assign($assignExpr, $match);
        if (! $prevInitializedAssign instanceof Assign) {
            return $assign;
        }

        $parentAssign = $prevInitializedAssign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentAssign instanceof Expression) {
            $this->removeNode($parentAssign);
        }

        return $assign;
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function resolveAssignExpr(array $condAndExprs): ?Expr
    {
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (! $expr instanceof Assign) {
                continue;
            }

            return $expr->var;
        }

        return null;
    }

    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function processImplicitReturnAfterSwitch(Switch_ $switch, Match_ $match, array $condAndExprs): Match_
    {
        $nextNode = $switch->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return $match;
        }

        $returnedExpr = $nextNode->expr;
        if (! $returnedExpr instanceof Expr) {
            return $match;
        }

        if ($this->matchSwitchAnalyzer->hasDefaultValue($match)) {
            return $match;
        }

        $this->removeNode($nextNode);

        $condAndExprs[] = new CondAndExpr([], $returnedExpr, MatchKind::RETURN);
        return $this->matchFactory->createFromCondAndExprs($switch->cond, $condAndExprs);
    }
}
