<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Core\Rector\AbstractRector;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector\SplitDoubleAssignRectorTest
 */
final class SplitDoubleAssignRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Split multiple inline assigns to each own lines default value, to prevent undefined array issues',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
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
        if (! $node->expr instanceof Assign) {
            return null;
        }

        $newAssign = new Assign($node->var, $node->expr->expr);

        $this->addNodeAfterNode($node->expr, $node);

        return $newAssign;
    }
}
