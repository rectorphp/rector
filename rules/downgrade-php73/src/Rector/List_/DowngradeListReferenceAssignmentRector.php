<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Rector\List_;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://wiki.php.net/rfc/list_reference_assignment
 * @see \Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\DowngradeListReferenceAssignmentRectorTest
 */
final class DowngradeListReferenceAssignmentRector extends AbstractRector
{
    /**
     * @var int
     */
    private const ALL = 0;

    /**
     * @var int
     */
    private const ANY = 1;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Convert the list reference assignment to its equivalent PHP 7.2 code',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        $array = [1, 2, 3];
        list($a, &$b) = $array;

        [&$c, $d, &$e] = $array;

        list(&$a, &$b) = $array;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($string)
    {
        $array = [1, 2];
        list($a) = $array;
        $b =& $array[1];

        [$c, $d, $e] = $array;
        $c =& $array[0];
        $e =& $array[2];

        $a =& $array[0];
        $b =& $array[1];
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
        return [List_::class, Array_::class];
    }

    /**
     * @param List_|Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->shouldRefactor($node)) {
            return null;
        }

        // Get all the params passed by reference
        /** @var Assign */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        /** @var Variable */
        $exprVariable = $parentNode->expr;
        // Count number of params by ref on the right side, to remove them later on
        $rightSideParamsByRefCount = $this->countRightSideMostParamsByRef($node->items);
        // Their position is kept in the array
        $newNodes = $this->createAssignRefArrayFromListReferences($node->items, $exprVariable, []);
        $this->addNodesAfterNode($newNodes, $node);
        // Remove the right-side-most params by reference from `list()`,
        // since they are not needed anymore
        // If all of them are by ref, then directly remove `list()`
        $nodeItemsCount = count($node->items);
        if ($rightSideParamsByRefCount === $nodeItemsCount) {
            // Remove the Assign node
            /** @var Assign */
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
            return null;
        }
        if ($rightSideParamsByRefCount > 0) {
            array_splice($node->items, $nodeItemsCount - $rightSideParamsByRefCount);
        }
        return $node;
    }

    /**
     * @param List_|Array_ $node
     */
    private function shouldRefactor(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        // Check it follows `list(...) = $foo`
        if ($parentNode instanceof Assign && $parentNode->var === $node && $parentNode->expr instanceof Variable) {
            return $this->hasAnyItemByRef($node->items);
        }

        return false;
    }

    /**
     * Count the number of params by reference placed at the end
     * These params are not needed anymore, so they can be removed
     * @param (ArrayItem|null)[] $listItems
     */
    private function countRightSideMostParamsByRef(array $listItems): int
    {
        // Their position is kept in the array
        $count = 0;
        $listItemsCount = count($listItems);
        // Start from the end => right-side-most params
        for ($i = $listItemsCount - 1; $i >= 0; $i--) {
            $listItem = $listItems[$i];
            // Also include null items, since they can be removed
            if ($listItem === null || $listItem->byRef) {
                $count++;
                continue;
            }
            // If it is a nested list, check if if all its items are by reference
            if ($listItem->value instanceof List_ || $listItem->value instanceof Array_) {
                // Recursive call
                /** @var List_|Array_ */
                $nestedList = $listItem->value;
                if ($this->hasAllItemsByRef($nestedList->items)) {
                    $count++;
                    continue;
                }
            }
            // Item not by reference. Reach the end
            return $count;
        }
        return $count;
    }

    /**
     * @param (ArrayItem|null)[] $listItems
     * @param (int|string)[] $nestedArrayIndexes
     * @return AssignRef[]
     */
    private function createAssignRefArrayFromListReferences(
        array $listItems,
        Variable $exprVariable,
        array $nestedArrayIndexes
    ): array {
        // After filtering, their original position is kept in the array
        $newNodes = [];
        foreach ($listItems as $position => $listItem) {
            if ($listItem === null) {
                continue;
            }
            // If it's a variable by value, not by reference, then skip
            if ($listItem->value instanceof Variable && ! $listItem->byRef) {
                continue;
            }
            // Access the key, if provided, or the position otherwise
            $key = $this->getArrayItemKey($listItem, $position);
            // Either the item is a variable, or a nested list
            if ($listItem->value instanceof Variable) {
                /** @var Variable */
                $itemVariable = $listItem->value;
                // Remove the reference in the present node
                $listItem->byRef = false;
                // In its place, assign the value by reference on a new node
                $assignVariable = new Variable($itemVariable->name);
                $newNodes[] = $this->createAssignRefWithArrayDimFetch(
                    $assignVariable,
                    $exprVariable,
                    $nestedArrayIndexes,
                    $key
                );
                continue;
            }
            // Nested list. Combine with the nodes from the recursive call
            /** @var List_ */
            $nestedList = $listItem->value;
            $listNestedArrayIndexes = array_merge($nestedArrayIndexes, [$key]);
            $newNodes = array_merge(
                $newNodes,
                $this->createAssignRefArrayFromListReferences(
                    $nestedList->items,
                    $exprVariable,
                    $listNestedArrayIndexes
                )
            );
        }
        return $newNodes;
    }

    /**
     * Return the key inside the ArrayItem, if provided, or the position otherwise
     * @param int|string $position
     * @return int|string
     */
    private function getArrayItemKey(
        ArrayItem $listItem,
        $position
    ) {
        if ($listItem->key !== null && ($listItem->key instanceof String_ || $listItem->key instanceof LNumber)) {
            return $listItem->key->value;
        }
        return $position;
    }

    /**
     * Indicates if there is at least 1 item passed by reference, as in:
     * - list(&$a, $b)
     * - list($a, $b, list(&$c, $d))
     *
     * @param (ArrayItem|null)[] $items
     */
    private function hasAnyItemByRef(array $items): bool
    {
        return count($this->getItemsByRef($items, self::ANY)) > 0;
    }

    /**
     * Indicates if there is all items are passed by reference, as in:
     * - list(&$a, &$b)
     * - list(&$a, &$b, list(&$c, &$d))
     *
     * @param (ArrayItem|null)[] $items
     */
    private function hasAllItemsByRef(array $items): bool
    {
        return count($this->getItemsByRef($items, self::ALL)) === count($items);
    }

    /**
     * Re-build the path to the variable with all accumulated indexes
     * @param (string|int)[] $nestedArrayIndexes The path to build nested lists
     * @param string|int $arrayIndex
     */
    private function createAssignRefWithArrayDimFetch(
        Variable $assignVariable,
        Variable $exprVariable,
        array $nestedArrayIndexes,
        $arrayIndex
    ): AssignRef {
        $nestedExprVariable = $exprVariable;
        foreach ($nestedArrayIndexes as $nestedArrayIndex) {
            $nestedArrayIndexDim = BuilderHelpers::normalizeValue($nestedArrayIndex);
            $nestedExprVariable = new ArrayDimFetch($nestedExprVariable, $nestedArrayIndexDim);
        }
        $dim = BuilderHelpers::normalizeValue($arrayIndex);
        $arrayDimFetch = new ArrayDimFetch($nestedExprVariable, $dim);
        return new AssignRef($assignVariable, $arrayDimFetch);
    }

    /**
     * @param (ArrayItem|null)[] $items
     * @return ArrayItem[]
     */
    private function getItemsByRef(array $items, int $condition): array
    {
        /** @var ArrayItem[] */
        return array_filter(array_map(
            function (?ArrayItem $item) use ($condition): ?ArrayItem {
                return $this->getItemByRefOrNull($item, $condition);
            },
            $items
        ));
    }

    /**
     * If the item is a variable by reference,
     * or a nested list containing variables by reference,
     * return the same item.
     * Otherwise, return null
     */
    private function getItemByRefOrNull(?ArrayItem $arrayItem, int $condition): ?ArrayItem
    {
        if ($this->isItemByRef($arrayItem, $condition)) {
            return $arrayItem;
        }
        return null;
    }

    /**
     * Indicate if the item is a variable by reference,
     * or a nested list containing variables by reference
     */
    private function isItemByRef(?ArrayItem $arrayItem, int $condition): bool
    {
        if ($arrayItem === null) {
            return false;
        }
        // Check if the item is a nested list/nested array destructuring
        $isNested = $arrayItem->value instanceof List_ || $arrayItem->value instanceof Array_;
        if ($isNested) {
            // Recursive call
            /** @var List_|Array_ */
            $nestedList = $arrayItem->value;
            if ($condition === self::ALL) {
                return $this->hasAllItemsByRef($nestedList->items);
            }
            // $condition === self::ANY
            return $this->hasAnyItemByRef($nestedList->items);
        }
        return $arrayItem->value instanceof Variable && $arrayItem->byRef;
    }
}
