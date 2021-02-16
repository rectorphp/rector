<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/dmHCC
 *
 * @see \Rector\CodeQuality\Tests\Rector\Expression\InlineIfToExplicitIfRector\InlineIfToExplicitIfRectorTest
 */
final class InlineIfToExplicitIfRector extends AbstractRector
{
    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change inline if to explicit if', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $userId = null;

        is_null($userId) && $userId = 5;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $userId = null;

        if (is_null($userId)) {
            $userId = 5;
        }
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
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof BooleanAnd && ! $node->expr instanceof BooleanOr) {
            return null;
        }

        return $this->processExplicitIf($node);
    }

    private function processExplicitIf(Expression $expression): ?Node
    {
        /** @var BooleanAnd|BooleanOr $booleanExpr */
        $booleanExpr = $expression->expr;

        $leftStaticType = $this->getStaticType($booleanExpr->left);
        if (! $leftStaticType instanceof BooleanType) {
            return null;
        }

        if (! $booleanExpr->right instanceof Assign) {
            return null;
        }

        /** @var Expr $expr */
        $expr = $booleanExpr instanceof BooleanAnd
            ? $booleanExpr->left
            : $this->binaryOpManipulator->inverseNode($booleanExpr->left);
        $if = new If_($expr);
        $if->stmts[] = new Expression($booleanExpr->right);

        return $if;
    }
}
