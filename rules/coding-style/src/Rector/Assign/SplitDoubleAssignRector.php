<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector\SplitDoubleAssignRectorTest
 */
final class SplitDoubleAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Split multiple inline assigns to each own lines default value, to prevent undefined array issues',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = $two = 1;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = 1;
        $two = 1;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Expression) {
            return null;
        }

        if (! $node->expr instanceof Assign) {
            return null;
        }

        $newAssign = new Assign($node->var, $node->expr->expr);

        if (! $this->isExprCallOrNew($node->expr->expr)) {
            $this->addNodeAfterNode($node->expr, $node);
            return $newAssign;
        }

        $varAssign = new Assign($node->expr->var, $node->var);
        $this->addNodeBeforeNode(new Expression($newAssign), $node);

        return $varAssign;
    }

    private function isExprCallOrNew(Expr $expr): bool
    {
        if ($expr instanceof MethodCall) {
            return true;
        }

        if ($expr instanceof StaticCall) {
            return true;
        }

        if ($expr instanceof FuncCall) {
            return true;
        }

        return $expr instanceof New_;
    }
}
