<?php

declare (strict_types=1);
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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/list_reference_assignment
 * @see \Rector\Tests\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector\DowngradeListReferenceAssignmentRectorTest
 */
final class DowngradeListReferenceAssignmentRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var int
     */
    private const ALL = 0;
    /**
     * @var int
     */
    private const ANY = 1;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert the list reference assignment to its equivalent PHP 7.2 code', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\List_::class, \PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param List_|Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->shouldRefactor($node)) {
            return null;
        }
        // Get all the params passed by reference
        /** @var Assign $parentNode */
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        /** @var Variable $exprVariable */
        $exprVariable = $parentNode->expr;
        // Count number of params by ref on the right side, to remove them later on
        $rightSideRemovableParamsCount = $this->countRightSideMostParamsByRefOrEmpty($node->items);
        // Add new nodes to do the assignment by reference
        $newNodes = $this->createAssignRefArrayFromListReferences($node->items, $exprVariable, []);
        $this->nodesToAddCollector->addNodesAfterNode($newNodes, $node);
        // Remove the stale params right-most-side
        return $this->removeStaleParams($node, $rightSideRemovableParamsCount);
    }
    /**
     * Remove the right-side-most params by reference or empty from `list()`,
     * since they are not needed anymore.
     * If all of them can be removed, then directly remove `list()`.
     * @return List_|Array_|null
     * @param \PhpParser\Node\Expr\List_|\PhpParser\Node\Expr\Array_ $node
     */
    public function removeStaleParams($node, int $rightSideRemovableParamsCount) : ?\PhpParser\Node
    {
        $nodeItemsCount = \count($node->items);
        if ($rightSideRemovableParamsCount === $nodeItemsCount) {
            // Remove the parent Assign node
            /** @var Assign $parentNode */
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            $this->removeNode($parentNode);
            return null;
        }
        if ($rightSideRemovableParamsCount > 0) {
            \array_splice($node->items, $nodeItemsCount - $rightSideRemovableParamsCount);
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\List_|\PhpParser\Node\Expr\Array_ $node
     */
    private function shouldRefactor($node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // Check it follows `list(...) = $foo`
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        if ($parentNode->var !== $node) {
            return \false;
        }
        if (!$parentNode->expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $this->hasAnyItemByRef($node->items);
    }
    /**
     * Count the number of params by reference placed at the end
     * These params are not needed anymore, so they can be removed
     * @param (ArrayItem|null)[] $listItems
     */
    private function countRightSideMostParamsByRefOrEmpty(array $listItems) : int
    {
        // Their position is kept in the array
        $count = 0;
        $listItemsCount = \count($listItems);
        // Start from the end => right-side-most params
        for ($i = $listItemsCount - 1; $i >= 0; --$i) {
            $listItem = $listItems[$i];
            // Also include null items, since they can be removed
            if (!$listItem instanceof \PhpParser\Node\Expr\ArrayItem || $listItem->byRef) {
                ++$count;
                continue;
            }
            // If it is a nested list, check if all its items are by reference
            $isNested = $listItem->value instanceof \PhpParser\Node\Expr\List_ || $listItem->value instanceof \PhpParser\Node\Expr\Array_;
            if ($isNested) {
                /** @var List_|Array_ $nestedList */
                $nestedList = $listItem->value;
                if ($this->hasAllItemsByRef($nestedList->items)) {
                    ++$count;
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
    private function createAssignRefArrayFromListReferences(array $listItems, \PhpParser\Node\Expr\Variable $exprVariable, array $nestedArrayIndexes) : array
    {
        // After filtering, their original position is kept in the array
        $newNodes = [];
        foreach ($listItems as $position => $listItem) {
            if (!$listItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($listItem->value instanceof \PhpParser\Node\Expr\Variable && !$listItem->byRef) {
                continue;
            }
            // Access the key, if provided, or the position otherwise
            $key = $this->getArrayItemKey($listItem, $position);
            // Either the item is a variable, or a nested list
            if ($listItem->value instanceof \PhpParser\Node\Expr\Variable) {
                /** @var Variable $itemVariable */
                $itemVariable = $listItem->value;
                // Remove the reference in the present node
                $listItem->byRef = \false;
                // In its place, assign the value by reference on a new node
                $assignVariable = new \PhpParser\Node\Expr\Variable($itemVariable->name);
                $newNodes[] = $this->createAssignRefWithArrayDimFetch($assignVariable, $exprVariable, $nestedArrayIndexes, $key);
                continue;
            }
            // Nested list. Combine with the nodes from the recursive call
            /** @var List_ $nestedList */
            $nestedList = $listItem->value;
            $listNestedArrayIndexes = \array_merge($nestedArrayIndexes, [$key]);
            $newNodes = \array_merge($newNodes, $this->createAssignRefArrayFromListReferences($nestedList->items, $exprVariable, $listNestedArrayIndexes));
        }
        return $newNodes;
    }
    /**
     * Indicates if there is at least 1 item passed by reference, as in:
     * - list(&$a, $b)
     * - list($a, $b, list(&$c, $d))
     *
     * @param (ArrayItem|null)[] $items
     */
    private function hasAnyItemByRef(array $items) : bool
    {
        return $this->getItemsByRef($items, self::ANY) !== [];
    }
    /**
     * Indicates if there is all items are passed by reference, as in:
     * - list(&$a, &$b)
     * - list(&$a, &$b, list(&$c, &$d))
     *
     * @param (ArrayItem|null)[] $items
     */
    private function hasAllItemsByRef(array $items) : bool
    {
        return \count($this->getItemsByRef($items, self::ALL)) === \count($items);
    }
    /**
     * Return the key inside the ArrayItem, if provided, or the position otherwise
     * @param int|string $position
     * @return int|string
     */
    private function getArrayItemKey(\PhpParser\Node\Expr\ArrayItem $arrayItem, $position)
    {
        if ($arrayItem->key instanceof \PhpParser\Node\Scalar\String_) {
            return $arrayItem->key->value;
        }
        if ($arrayItem->key instanceof \PhpParser\Node\Scalar\LNumber) {
            return $arrayItem->key->value;
        }
        return $position;
    }
    /**
     * Re-build the path to the variable with all accumulated indexes
     * @param (string|int)[] $nestedArrayIndexes The path to build nested lists
     * @param string|int $arrayIndex
     */
    private function createAssignRefWithArrayDimFetch(\PhpParser\Node\Expr\Variable $assignVariable, \PhpParser\Node\Expr\Variable $exprVariable, array $nestedArrayIndexes, $arrayIndex) : \PhpParser\Node\Expr\AssignRef
    {
        $nestedExprVariable = $exprVariable;
        foreach ($nestedArrayIndexes as $nestedArrayIndex) {
            $nestedArrayIndexDim = \PhpParser\BuilderHelpers::normalizeValue($nestedArrayIndex);
            $nestedExprVariable = new \PhpParser\Node\Expr\ArrayDimFetch($nestedExprVariable, $nestedArrayIndexDim);
        }
        $dim = \PhpParser\BuilderHelpers::normalizeValue($arrayIndex);
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($nestedExprVariable, $dim);
        return new \PhpParser\Node\Expr\AssignRef($assignVariable, $arrayDimFetch);
    }
    /**
     * @param array<ArrayItem|null> $arrayItems
     * @return ArrayItem[]
     */
    private function getItemsByRef(array $arrayItems, int $condition) : array
    {
        $itemsByRef = [];
        foreach ($arrayItems as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if (!$this->isItemByRef($arrayItem, $condition)) {
                continue;
            }
            $itemsByRef[] = $arrayItem;
        }
        return $itemsByRef;
    }
    /**
     * Indicate if the item is a variable by reference,
     * or a nested list containing variables by reference
     */
    private function isItemByRef(\PhpParser\Node\Expr\ArrayItem $arrayItem, int $condition) : bool
    {
        // Check if the item is a nested list/nested array destructuring
        $isNested = $arrayItem->value instanceof \PhpParser\Node\Expr\List_ || $arrayItem->value instanceof \PhpParser\Node\Expr\Array_;
        if ($isNested) {
            // Recursive call
            /** @var List_|Array_ $nestedList */
            $nestedList = $arrayItem->value;
            if ($condition === self::ALL) {
                return $this->hasAllItemsByRef($nestedList->items);
            }
            // $condition === self::ANY
            return $this->hasAnyItemByRef($nestedList->items);
        }
        if (!$arrayItem->value instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $arrayItem->byRef;
    }
}
