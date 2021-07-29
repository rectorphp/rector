<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeAnalyzer\CoalesceAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/throw_expression
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\DowngradeThrowExprRectorTest
 */
final class DowngradeThrowExprRector extends AbstractRector
{
    public function __construct(
        private IfManipulator $ifManipulator,
        private CoalesceAnalyzer $coalesceAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade throw as expr', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $id = $somethingNonexistent ?? throw new RuntimeException();
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (!isset($somethingNonexistent)) {
            throw new RuntimeException();
        }
        $id = $somethingNonexistent;
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
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->expr instanceof Throw_) {
            return null;
        }

        if ($node->expr instanceof Assign) {
            return $this->processAssign($node, $node->expr);
        }

        return $node;
    }

    private function processAssign(Expression $expression, Assign $assign): If_ | Expression | null
    {
        if (! $this->hasThrowInAssignExpr($assign)) {
            return null;
        }

        if ($assign->expr instanceof Coalesce) {
            return $this->processCoalesce($assign, $assign->expr);
        }

        if ($assign->expr instanceof Throw_) {
            return new Expression(($assign->expr));
        }

        return $expression;
    }

    private function processCoalesce(Assign $assign, Coalesce $coalesce): ?If_
    {
        if (! $coalesce->right instanceof Throw_) {
            return null;
        }

        if (! $this->coalesceAnalyzer->hasIssetableLeft($coalesce)) {
            return null;
        }

        $booleanNot = new BooleanNot(new Isset_([$coalesce->left]));
        $assign->expr = $coalesce->left;

        $if = $this->ifManipulator->createIfExpr($booleanNot, new Expression($coalesce->right));

        $this->addNodeAfterNode(new Expression($assign), $if);
        return $if;
    }

    private function hasThrowInAssignExpr(Assign $assign): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $assign->expr,
            fn (Node $node): bool => $node instanceof Throw_
        );
    }
}
