<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\DowngradeArraySpreadRectorTest
 */
final class DowngradeArraySpreadRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace array spread with array_merge function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = ['banana', 'orange', ...$parts, 'watermelon'];
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
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
        return [Array_::class];
    }

    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->shouldRefactor($node)) {
            return null;
        }
        // Iterate all array items:
        // 1. If they are the spread, replace with the normal variable
        // 2. If not, make them part of an array
        $newItems = [];
        $accumulatedItems = [];
        foreach ($node->items as $item) {
            if ($item !== null && $item->unpack) {
                // Spread operator found
                if ($accumulatedItems) {
                    // If previous items were in the new array, add them first
                    $newItems[] = $this->createArrayItem($accumulatedItems);
                    // Reset the accumulated items
                    $accumulatedItems = [];
                }
                // Add the current item, still with "unpack = true" (it will be removed later on)
                $newItems[] = $item;
                continue;
            }

            // Normal item, it goes into the accumulated array
            $accumulatedItems[] = $item;
        }
        // Add the remaining accumulated items
        if ($accumulatedItems) {
            $newItems[] = $this->createArrayItem($accumulatedItems);
        }
        // Replace this array node with an `array_merge`
        return $this->createArrayMerge($newItems);
    }

    private function shouldRefactor(Array_ $node): bool
    {
        // Check that any item in the array is the spread
        return count(array_filter($node->items, function (?ArrayItem $item) {
            return $item !== null && $item->unpack;
        })) > 0;
    }

    /**
     * @param (ArrayItem|null)[] $items
     */
    private function createArrayItem(array $items): ArrayItem
    {
        return new ArrayItem(new Array_($items));
    }

    /**
     * @see https://wiki.php.net/rfc/spread_operator_for_array
     * @param (ArrayItem|null)[] $items
     */
    private function createArrayMerge(array $items): FuncCall
    {
        return new FuncCall(new Name('array_merge'), array_map(function (ArrayItem $item) {
            if ($item !== null && $item->unpack) {
                // Do not unpack anymore
                $item->unpack = false;
                // array_merge only supports array, while spread operator also supports objects implementing Traversable.
                return new Arg(
                    new Ternary(
                        new FuncCall(new Name('is_array'), [new Arg($item)]),
                        $item,
                        new FuncCall(new Name('iterator_to_array'), [new Arg($item)])
                    )
                );
            }
            return new Arg($item);
        }, $items));
    }
}
