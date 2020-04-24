<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\UnusedForeachValueToArrayKeysRectorTest
 */
final class UnusedForeachValueToArrayKeysRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change foreach with unused $value but only $key, to array_keys()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];
        foreach ($values as $key => $value) {
            $items[$key] = null;
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];
        foreach (array_keys($values) as $key) {
            $items[$key] = null;
        }
    }
}
PHP

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

        $valueNode = $node->valueVar;
        $isValueUsed = (bool) $this->betterNodeFinder->findFirst($node->stmts, function (Node $node) use ($valueNode) {
            return $this->areNodesEqual($node, $valueNode);
        });

        if ($isValueUsed) {
            return null;
        }

        // remove key value
        $node->valueVar = $node->keyVar;
        $node->keyVar = null;

        $node->expr = $this->createFuncCall('array_keys', [$node->expr]);

        return $node;
    }
}
