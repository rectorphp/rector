<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Rector\List_;

use PhpParser\Node;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\ArrayDimFetch;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/list_reference_assignment
 * @see \Rector\DowngradePhp73\Tests\Rector\List_\DowngradeListReferenceAssignmentRector\DowngradeListReferenceAssignmentRectorTest
 */
final class DowngradeListReferenceAssignmentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Convert `list()` reference assignment to PHP 7.2 code: `list($a, &$b) = $array;` => `list($a, $b) = $array; $b =& $array[1];`',
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
     * @param (ArrayItem|null)[] $listItems
     * @param (int|string)[] $nestedKeys
     * @return AssignRef[]
     */
    public function createAssignRefArrayFromListReferences(array $listItems, Variable $exprVariable, array $nestedKeys): array
    {
        // Their position is kept in the array
        $newNodes = [];
        foreach ($listItems as $position => $listItem) {
            if ($listItem->value instanceof Variable && !$listItem->byRef) {
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
                $newNodes[] = $this->createAssignRefWithArrayDimFetch($assignVariable, $exprVariable, $nestedKeys, $key);
            } else {
                /** @var List_ */
                $nestedList = $listItem->value;
                $newNodes = array_merge(
                    $newNodes,
                    $this->createAssignRefArrayFromListReferences($nestedList->items, $exprVariable, array_merge($nestedKeys, [$key]))
                );
            }
        }
        return $newNodes;
    }

    /**
     * @param List_|Array_ $node
     */
    private function shouldRefactor(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        // Check it follows `list(...) = $foo`
        if ($parentNode instanceof Assign && $parentNode->var === $node && $parentNode->expr instanceof Variable) {
            // There must be at least one param by reference
            return $this->hasItemsByRef($node->items);
        }

        return false;
    }

    /**
     * @param (ArrayItem|null)[] $items
     */
    private function hasItemsByRef(array $items): bool
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
                // Check if the item is a nested list
                if ($item->value instanceof List_) {
                    // Recursive call
                    /** @var List_ */
                    $nestedList = $item->value;
                    $hasItemsByRef = $this->hasItemsByRef($nestedList->items);
                    return $hasItemsByRef ? $item : null;
                }
                return $item->value instanceof Variable && $item->byRef ? $item : null;
            },
            $items
        ));
        return count($filteredItemsByRef) > 0;
    }

    /**
     * @param (string|int)[] $nestedKeys
     * @param string|int $dimValue
     */
    private function createAssignRefWithArrayDimFetch(
        Variable $assignVariable,
        Variable $exprVariable,
        array $nestedKeys,
        $dimValue
    ): AssignRef {
        $nestedExprVariable = $exprVariable;
        foreach ($nestedKeys as $nestedKey) {
            $nestedKeyDim = BuilderHelpers::normalizeValue($nestedKey);
            $nestedExprVariable = new ArrayDimFetch($nestedExprVariable, $nestedKeyDim);
        }
        $dim = BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new ArrayDimFetch($nestedExprVariable, $dim);
        return new AssignRef($assignVariable, $arrayDimFetch);
    }
}
