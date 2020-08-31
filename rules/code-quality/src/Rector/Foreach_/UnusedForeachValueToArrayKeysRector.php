<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Variable;
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

        // special case of nested array items
        if ($node->valueVar instanceof Array_) {
            $node->valueVar = $this->refactorArrayForeachValue($node->valueVar, $node);
            if (count($node->valueVar->items) > 0) {
                return null;
            }
        } elseif ($node->valueVar instanceof Variable) {
            if ($this->isVariableUsedInForeach($node->valueVar, $node)) {
                return null;
            }
        } else {
            return null;
        }

        $this->removeForeachValueAndUseArrayKeys($node);

        return $node;
    }

    private function refactorArrayForeachValue(Array_ $array, Foreach_ $foreach): Array_
    {
        foreach ($array->items as $key => $arrayItem) {
            $value = $arrayItem->value;
            if (! $value instanceof Variable) {
                return $array;
            }

            if ($this->isVariableUsedInForeach($value, $foreach)) {
                continue;
            }

            unset($array->items[$key]);
        }

        return $array;
    }

    private function isVariableUsedInForeach(Variable $variable, Foreach_ $foreach): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($foreach->stmts, function (Node $node) use ($variable): bool {
            return $this->areNodesEqual($node, $variable);
        });
    }

    private function removeForeachValueAndUseArrayKeys(Foreach_ $foreach): void
    {
        // remove key value
        $foreach->valueVar = $foreach->keyVar;
        $foreach->keyVar = null;

        $foreach->expr = $this->createFuncCall('array_keys', [$foreach->expr]);
    }
}
