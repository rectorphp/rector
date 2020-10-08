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
        list($a, $b) = $array;
        $b =& $array[1];

        [&$c, $d, &$e] = $array;
        $c =& $array[0];
        $e =& $array[2];
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
        // Their position is kept in the array
        $newNodes = $this->createAssignRefArrayFromListReferences($node->items, $exprVariable, []);
        $this->addNodesAfterNode($newNodes, $node);
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
            return $this->hasItemByRef($node->items);
        }

        return false;
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
        // Their position is kept in the array
        $newNodes = [];
        foreach ($listItems as $position => $listItem) {
            if ($listItem === null) {
                continue;
            }
            // If it's a variable by value, not by reference, then skip
            if ($listItem->value instanceof Variable && ! $listItem->byRef) {
                continue;
            }
            // Access the array under the key, if provided, or the position otherwise
            $key = $position;
            if ($listItem->key !== null && $listItem->key instanceof String_) {
                $key = $listItem->key->value;
            }
            // Either the item is a variable, or a nested list
            if ($listItem->value instanceof Variable) {
                // Change to not assign by reference in the present node
                $listItem->byRef = false;
                /** @var Variable */
                $itemVariable = $listItem->value;
                // Assign the value by reference on a new assignment
                $assignVariable = new Variable($itemVariable->name);
                $newNodes[] = $this->createAssignRefWithArrayDimFetch(
                    $assignVariable,
                    $exprVariable,
                    $nestedArrayIndexes,
                    $key
                );
            } else {
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
    private function hasItemByRef(array $items): bool
    {
        /** @var ArrayItem[] */
        $filteredItemsByRef = array_filter(array_map(
            /**
             * @var ArrayItem|null $item
             */
            function ($item): ?ArrayItem {
                if ($item === null) {
                    return null;
                }
                // Check if the item is a nested list/nested array destructuring
                if ($item->value instanceof List_ || $item->value instanceof Array_) {
                    // Recursive call
                    /** @var List_|Array_ */
                    $nestedList = $item->value;
                    $hasItemByRef = $this->hasItemByRef($nestedList->items);
                    return $hasItemByRef ? $item : null;
                }
                return $item->value instanceof Variable && $item->byRef ? $item : null;
            },
            $items
        ));
        return count($filteredItemsByRef) > 0;
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
}
