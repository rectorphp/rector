<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
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
        if (! $node->expr instanceof BooleanAnd) {
            return null;
        }

        $booleanAnd = $node->expr;

        $leftStaticType = $this->getStaticType($booleanAnd->left);
        if (! $leftStaticType instanceof BooleanType) {
            return null;
        }

        if (! $booleanAnd->right instanceof Assign) {
            return null;
        }

        $if = new If_($booleanAnd->left);
        $if->stmts[] = new Expression($booleanAnd->right);

        return $if;
    }
}
