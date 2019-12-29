<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/f7itn
 *
 * @see \Rector\CodeQuality\Tests\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\ArrayKeyExistsTernaryThenValueToCoalescingRectorTest
 */
final class ArrayKeyExistsTernaryThenValueToCoalescingRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array_key_exists() ternary to coalesing', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($values, $keyToMatch)
    {
        $result = array_key_exists($keyToMatch, $values) ? $values[$keyToMatch] : null;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($values, $keyToMatch)
    {
        $result = $values[$keyToMatch] ?? null;
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
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->cond instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($node->cond, 'array_key_exists')) {
            return null;
        }

        if (! $node->if instanceof ArrayDimFetch) {
            return null;
        }

        if (! $this->areArrayKeysExistsArgsMatchingDimFetch($node->cond, $node->if)) {
            return null;
        }

        return new Coalesce($node->if, $node->else);
    }

    /**
     * Equals if:
     *
     * array_key_exists($key, $values);
     * =
     * $values[$key]
     */
    private function areArrayKeysExistsArgsMatchingDimFetch(FuncCall $funcCall, ArrayDimFetch $arrayDimFetch): bool
    {
        $keyExpr = $funcCall->args[0]->value;
        $valuesExpr = $funcCall->args[1]->value;

        if (! $this->areNodesEqual($arrayDimFetch->var, $valuesExpr)) {
            return false;
        }
        return $this->areNodesEqual($arrayDimFetch->dim, $keyExpr);
    }
}
