<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\EarlyReturn\Tests\Rector\Return_\PreparedValueToEarlyReturnRector\PreparedValueToEarlyReturnRectorTest
 */
final class PreparedValueToEarlyReturnRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Return early prepared value in ifs', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $var = null;

        if (rand(0,1)) {
            $var = 1;
        }

        if (rand(0,1)) {
            $var = 2;
        }

        return $var;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (rand(0,1)) {
            return 1;
        }

        if (rand(0,1)) {
            return 2;
        }

        return null;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $ifsBefore = $this->getIfsBefore($node);

        /** @var Expr $returnExpr */
        $returnExpr = $node->expr;
        /** @var Expression $previousFirstExpression */
        $previousFirstExpression = $this->getPreviousIfLinearEquals($ifsBefore[0], $returnExpr);

        foreach ($ifsBefore as $ifBefore) {
            /** @var Expression $expressionIf */
            $expressionIf = $ifBefore->stmts[0];
            /** @var Assign $assignIf */
            $assignIf = $expressionIf->expr;

            $ifBefore->stmts[0] = new Return_($assignIf->expr);
        }

        /** @var Assign $assignPrevious */
        $assignPrevious = $previousFirstExpression->expr;
        $node->expr = $assignPrevious->expr;
        $this->removeNode($previousFirstExpression);

        return $node;
    }

    private function shouldSkip(Return_ $return): bool
    {
        $returnExpr = $return->expr;
        if (! $returnExpr instanceof Expr) {
            return false;
        }

        $ifsBefore = $this->getIfsBefore($return);
        if ($ifsBefore === []) {
            return true;
        }

        return ! (bool) $this->getPreviousIfLinearEquals($ifsBefore[0], $returnExpr);
    }

    private function getPreviousIfLinearEquals(If_ $if, Expr $expr): ?Expression
    {
        $previous = $if->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previous instanceof Expression) {
            return null;
        }

        if (! $previous->expr instanceof Assign) {
            return null;
        }

        if ($this->areNodesEqual($previous->expr->var, $expr)) {
            return $previous;
        }

        return null;
    }

    /**
     * @return If_[]
     */
    private function getIfsBefore(Return_ $return): array
    {
        $parent = $return->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof FunctionLike && ! $parent instanceof If_) {
            return [];
        }

        if (! (property_exists($parent, 'stmts') && $parent->stmts !== null)) {
            return [];
        }

        if ($parent->stmts[(is_countable($parent->stmts) ? count($parent->stmts) : 0) - 1] !== $return) {
            return [];
        }

        return $this->collectIfs($parent, $return);
    }

    /**
     * @param FunctionLike|If_ $node
     * @return If_[]
     */
    private function collectIfs(Node $node, Return_ $return): array
    {
        if (! (property_exists($node, 'stmts') && $node->stmts !== null)) {
            return [];
        }

        /** @va If_[] $ifs */
        $ifs = $this->betterNodeFinder->findInstanceOf($node->stmts, If_::class);

        /** Skip entirely if found skipped ifs */
        foreach ($ifs as $if) {
            /** @var If_ $if */
            if ($if->else instanceof Else_) {
                return [];
            }

            if ($if->elseifs !== []) {
                return [];
            }

            if (count($if->stmts) !== 1) {
                return [];
            }

            $expression = $if->stmts[0];
            if (! $expression instanceof Expression) {
                return [];
            }

            if (! $expression->expr instanceof Assign) {
                return [];
            }

            $assign = $expression->expr;
            if (! $this->areNodesEqual($assign->var, $return->expr)) {
                return [];
            }
        }

        return $ifs;
    }
}
