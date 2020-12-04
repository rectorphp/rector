<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Foreach_\RemoveUnusedForeachKeyRector\RemoveUnusedForeachKeyRectorTest
 */
final class RemoveUnusedForeachKeyRector extends AbstractRector
{
    public $testSamples = true;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused key in foreach', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$items = [];
foreach ($items as $key => $value) {
    $result = $value;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$items = [];
foreach ($items as $value) {
    $result = $value;
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
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->keyVar === null) {
            return null;
        }

        $keyVar = $node->keyVar;

        $isNodeUsed = (bool) $this->betterNodeFinder->findFirst($node->stmts, function (Node $node) use (
            $keyVar
        ): bool {
            return $this->areNodesEqual($node, $keyVar);
        });

        if ($isNodeUsed) {
            return null;
        }

        $node->keyVar = null;

        return $node;
    }
}
