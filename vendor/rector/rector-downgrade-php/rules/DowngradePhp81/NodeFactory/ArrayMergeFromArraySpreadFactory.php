<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\Application\File;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ArrayMergeFromArraySpreadFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer
     */
    private $arraySpreadAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ArraySpreadAnalyzer $arraySpreadAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arraySpreadAnalyzer = $arraySpreadAnalyzer;
    }
    public function createFromArray(Array_ $array, MutatingScope $mutatingScope, File $file) : ?Node
    {
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($array)) {
            return null;
        }
        $newArrayItems = $this->disolveArrayItems($array);
        return $this->createArrayMergeFuncCall($newArrayItems, $mutatingScope);
    }
    /**
     * Iterate all array items:
     *
     * 1. If they use the spread, remove it
     * 2. If not, make the item part of an accumulating array,
     *    to be added once the next spread is found, or at the end
     * @return ArrayItem[]
     */
    private function disolveArrayItems(Array_ $array) : array
    {
        $newItems = [];
        $accumulatedItems = [];
        foreach ($array->items as $item) {
            if ($item instanceof ArrayItem && $item->unpack) {
                if ($accumulatedItems !== []) {
                    // If previous items were in the new array, add them first
                    $newItems[] = $this->createArrayItemFromArray($accumulatedItems);
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
        if ($accumulatedItems !== []) {
            $newItems[] = $this->createArrayItemFromArray($accumulatedItems);
        }
        return $newItems;
    }
    /**
     * @param ArrayItem[] $arrayItems
     */
    private function createArrayMergeFuncCall(array $arrayItems, MutatingScope $mutatingScope) : FuncCall
    {
        $args = \array_map(function (ArrayItem $arrayItem) use($mutatingScope) : Arg {
            if ($arrayItem->unpack) {
                // Do not unpack anymore
                $arrayItem->unpack = \false;
                return $this->createArgFromSpreadArrayItem($mutatingScope, $arrayItem);
            }
            return new Arg($arrayItem);
        }, $arrayItems);
        return new FuncCall(new Name('array_merge'), $args);
    }
    /**
     * @param array<ArrayItem|null> $items
     */
    private function createArrayItemFromArray(array $items) : ArrayItem
    {
        $array = new Array_($items);
        return new ArrayItem($array);
    }
    private function createArgFromSpreadArrayItem(MutatingScope $mutatingScope, ArrayItem $arrayItem) : Arg
    {
        // By now every item is a variable
        /** @var Variable $variable */
        $variable = $arrayItem->value;
        $variableName = $this->nodeNameResolver->getName($variable) ?? '';
        // If the variable is not in scope, it's one we just added.
        // Then get the type from the attribute
        if ($mutatingScope->hasVariableType($variableName)->yes()) {
            $type = $mutatingScope->getVariableType($variableName);
        } else {
            $originalNode = $arrayItem->getAttribute(AttributeKey::ORIGINAL_NODE);
            if ($originalNode instanceof ArrayItem) {
                $type = $mutatingScope->getType($originalNode->value);
            } else {
                throw new ShouldNotHappenException();
            }
        }
        $iteratorToArrayFuncCall = new FuncCall(new Name('iterator_to_array'), [new Arg($arrayItem)]);
        // If we know it is an array, then print it directly
        // Otherwise PHPStan throws an error:
        // "Else branch is unreachable because ternary operator condition is always true."
        if ($type instanceof ArrayType) {
            return new Arg($arrayItem);
        }
        // If it is iterable, then directly return `iterator_to_array`
        if ($this->isIterableType($type)) {
            return new Arg($iteratorToArrayFuncCall);
        }
        // Print a ternary, handling either an array or an iterator
        $inArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($arrayItem)]);
        return new Arg(new Ternary($inArrayFuncCall, $arrayItem, $iteratorToArrayFuncCall));
    }
    /**
     * Iterables: objects declaring the interface Traversable,
     * For "iterable" type, it can be array
     */
    private function isIterableType(Type $type) : bool
    {
        if ($type instanceof IterableType) {
            return \false;
        }
        $traversableObjectType = new ObjectType('Traversable');
        return $traversableObjectType->isSuperTypeOf($type)->yes();
    }
}
