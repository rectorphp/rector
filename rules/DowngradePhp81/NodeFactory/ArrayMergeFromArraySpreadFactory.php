<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\Application\File;
use Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
final class ArrayMergeFromArraySpreadFactory
{
    /**
     * @var bool
     */
    private $shouldIncrement = \false;
    /**
     * Handle different result in CI
     *
     * @var array<string, int>
     */
    private $lastPositionCurrentFile = [];
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
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
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\DowngradePhp81\NodeAnalyzer\ArraySpreadAnalyzer $arraySpreadAnalyzer)
    {
        $this->variableNaming = $variableNaming;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arraySpreadAnalyzer = $arraySpreadAnalyzer;
    }
    public function createFromArray(\PhpParser\Node\Expr\Array_ $array, \PHPStan\Analyser\Scope $scope, \Rector\Core\ValueObject\Application\File $file, ?bool $shouldIncrement = null) : ?\PhpParser\Node
    {
        if (!$this->arraySpreadAnalyzer->isArrayWithUnpack($array)) {
            return null;
        }
        if ($shouldIncrement !== null) {
            $this->shouldIncrement = $shouldIncrement;
        } else {
            $this->shouldIncrement = (bool) $this->betterNodeFinder->findFirstNext($array, function (\PhpParser\Node $subNode) : bool {
                if (!$subNode instanceof \PhpParser\Node\Expr\Array_) {
                    return \false;
                }
                return $this->arraySpreadAnalyzer->isArrayWithUnpack($subNode);
            });
        }
        $newArrayItems = $this->disolveArrayItems($array, $scope, $file);
        return $this->createArrayMergeFuncCall($newArrayItems, $scope);
    }
    /**
     * Iterate all array items:
     *
     * 1. If they use the spread, remove it
     * 2. If not, make the item part of an accumulating array,
     *    to be added once the next spread is found, or at the end
     * @return ArrayItem[]
     */
    private function disolveArrayItems(\PhpParser\Node\Expr\Array_ $array, \PHPStan\Analyser\Scope $scope, \Rector\Core\ValueObject\Application\File $file) : array
    {
        $newItems = [];
        $accumulatedItems = [];
        foreach ($array->items as $position => $item) {
            if ($item !== null && $item->unpack) {
                // Spread operator found
                if (!$item->value instanceof \PhpParser\Node\Expr\Variable) {
                    // If it is a not variable, transform it to a variable
                    $item->value = $this->createVariableFromNonVariable($array, $item, $position, $scope, $file);
                }
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
    private function createArrayMergeFuncCall(array $arrayItems, \PHPStan\Analyser\Scope $scope) : \PhpParser\Node\Expr\FuncCall
    {
        $args = \array_map(function (\PhpParser\Node\Expr\ArrayItem $arrayItem) use($scope) : Arg {
            if ($arrayItem->unpack) {
                // Do not unpack anymore
                $arrayItem->unpack = \false;
                return $this->createArgFromSpreadArrayItem($scope, $arrayItem);
            }
            return new \PhpParser\Node\Arg($arrayItem);
        }, $arrayItems);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_merge'), $args);
    }
    /**
     * If it is a variable, we add it directly
     * Otherwise it could be a function, method, ternary, traversable, etc
     * We must then first extract it into a variable,
     * as to invoke it only once and avoid potential bugs,
     * such as a method executing some side-effect
     */
    private function createVariableFromNonVariable(\PhpParser\Node\Expr\Array_ $array, \PhpParser\Node\Expr\ArrayItem $arrayItem, int $position, \PHPStan\Analyser\Scope $scope, \Rector\Core\ValueObject\Application\File $file) : \PhpParser\Node\Expr\Variable
    {
        // The variable name will be item0Unpacked, item1Unpacked, etc,
        // depending on their position.
        // The number can't be at the end of the var name, or it would
        // conflict with the counter (for if that name is already taken)
        $smartFileInfo = $file->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPath();
        $position = $this->lastPositionCurrentFile[$realPath] ?? $position;
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName($array, $scope, 'item' . $position . 'Unpacked');
        if ($this->shouldIncrement) {
            $this->lastPositionCurrentFile[$realPath] = ++$position;
        }
        // Assign the value to the variable, and replace the element with the variable
        $newVariable = new \PhpParser\Node\Expr\Variable($variableName);
        $newVariableAssign = new \PhpParser\Node\Expr\Assign($newVariable, $arrayItem->value);
        $this->nodesToAddCollector->addNodeBeforeNode($newVariableAssign, $array, $file->getSmartFileInfo());
        return $newVariable;
    }
    /**
     * @param array<ArrayItem|null> $items
     */
    private function createArrayItemFromArray(array $items) : \PhpParser\Node\Expr\ArrayItem
    {
        $array = new \PhpParser\Node\Expr\Array_($items);
        return new \PhpParser\Node\Expr\ArrayItem($array);
    }
    private function createArgFromSpreadArrayItem(\PHPStan\Analyser\Scope $nodeScope, \PhpParser\Node\Expr\ArrayItem $arrayItem) : \PhpParser\Node\Arg
    {
        // By now every item is a variable
        /** @var Variable $variable */
        $variable = $arrayItem->value;
        $variableName = $this->nodeNameResolver->getName($variable) ?? '';
        // If the variable is not in scope, it's one we just added.
        // Then get the type from the attribute
        if ($nodeScope->hasVariableType($variableName)->yes()) {
            $type = $nodeScope->getVariableType($variableName);
        } else {
            $originalNode = $arrayItem->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
            if ($originalNode instanceof \PhpParser\Node\Expr\ArrayItem) {
                $type = $nodeScope->getType($originalNode->value);
            } else {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
        }
        $iteratorToArrayFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('iterator_to_array'), [new \PhpParser\Node\Arg($arrayItem)]);
        // If we know it is an array, then print it directly
        // Otherwise PHPStan throws an error:
        // "Else branch is unreachable because ternary operator condition is always true."
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return new \PhpParser\Node\Arg($arrayItem);
        }
        // If it is iterable, then directly return `iterator_to_array`
        if ($this->isIterableType($type)) {
            return new \PhpParser\Node\Arg($iteratorToArrayFuncCall);
        }
        // Print a ternary, handling either an array or an iterator
        $inArrayFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('is_array'), [new \PhpParser\Node\Arg($arrayItem)]);
        return new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Ternary($inArrayFuncCall, $arrayItem, $iteratorToArrayFuncCall));
    }
    /**
     * Iterables: objects declaring the interface Traversable,
     * For "iterable" type, it can be array
     */
    private function isIterableType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\IterableType) {
            return \false;
        }
        $traversableObjectType = new \PHPStan\Type\ObjectType('Traversable');
        return $traversableObjectType->isSuperTypeOf($type)->yes();
    }
}
