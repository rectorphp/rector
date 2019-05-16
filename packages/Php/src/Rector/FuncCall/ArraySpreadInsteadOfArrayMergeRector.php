<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/spread_operator_for_array
 */
final class ArraySpreadInsteadOfArrayMergeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array_merge() to spread operator', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($iter1, $iter2)
    {
        $values = array_merge(iterator_to_array($iter1), iterator_to_array($iter2));

        // Or to generalize to all iterables
        $anotherValues = array_merge(
            is_array($iter1) ? $iter1 : iterator_to_array($iter1),
            is_array($iter2) ? $iter2 : iterator_to_array($iter2)
        );
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($iter1, $iter2)
    {
        $values = [ ...$iter1, ...$iter2 ];

        // Or to generalize to all iterables
        $anotherValues = [ ...$iter1, ...$iter2 ];
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isName($node, 'array_merge')) {
            return $this->refactorArray($node);
        }

        if ($this->isName($node, 'iterator_to_array')) {
            return $this->refactorIteratorToArray($node);
        }

        return null;
    }

    private function isIteratorToArrayFuncCall(Node\Expr $expr): bool
    {
        return $expr instanceof Node\Expr\FuncCall && $this->isName($expr, 'iterator_to_array');
    }

    private function resolveValue(Node\Expr $expr): Node\Expr
    {
        if ($this->isIteratorToArrayFuncCall($expr)) {
            /** @var FuncCall $expr */
            $expr = $expr->args[0]->value;
        }

        if (! $expr instanceof Node\Expr\Ternary) {
            return $expr;
        }

        if (! $expr->cond instanceof Node\Expr\FuncCall) {
            return $expr;
        }

        if (! $this->isName($expr->cond, 'is_array')) {
            return $expr;
        }

        if ($expr->if instanceof Node\Expr\Variable) {
            if ($this->isIteratorToArrayFuncCall($expr->else)) {
                return $expr->if;
            }
        }

        return $expr;
    }

    private function refactorArray(Node $node): Node\Expr\Array_
    {
        $array = new Node\Expr\Array_();

        foreach ($node->args as $arg) {
            $value = $arg->value;
            $value = $this->resolveValue($value);

            $array->items[] = $this->createUnpackedArrayItem($value);
        }

        return $array;
    }

    private function refactorIteratorToArray(FuncCall $funcCall): Node\Expr\Array_
    {
        $array = new Node\Expr\Array_();
        $array->items[] = $this->createUnpackedArrayItem($funcCall->args[0]->value);

        return $array;
    }

    private function createUnpackedArrayItem(Node\Expr $expr): ArrayItem
    {
        return new ArrayItem($expr, null, false, [], true);
    }
}
