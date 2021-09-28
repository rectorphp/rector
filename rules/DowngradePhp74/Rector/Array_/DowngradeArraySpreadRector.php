<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Array_;

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
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Traversable;
/**
 * @changelog https://wiki.php.net/rfc/spread_operator_for_array
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector\DowngradeArraySpreadRectorTest
 */
final class DowngradeArraySpreadRector extends \Rector\Core\Rector\AbstractRector
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
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace array spread with array_merge function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = ['banana', 'orange', ...$parts, 'watermelon'];
    }

    public function runWithIterable()
    {
        $fruits = ['banana', 'orange', ...new ArrayIterator(['durian', 'kiwi']), 'watermelon'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $parts = ['apple', 'pear'];
        $fruits = array_merge(['banana', 'orange'], $parts, ['watermelon']);
    }

    public function runWithIterable()
    {
        $item0Unpacked = new ArrayIterator(['durian', 'kiwi']);
        $fruits = array_merge(['banana', 'orange'], is_array($item0Unpacked) ? $item0Unpacked : iterator_to_array($item0Unpacked), ['watermelon']);
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
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->shouldRefactor($node)) {
            return null;
        }
        $this->shouldIncrement = (bool) $this->betterNodeFinder->findFirstNext($node, function (\PhpParser\Node $subNode) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\Array_) {
                return \false;
            }
            return $this->shouldRefactor($subNode);
        });
        return $this->refactorNode($node);
    }
    private function shouldRefactor(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        // Check that any item in the array is the spread
        foreach ($array->items as $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($item->unpack) {
                return \true;
            }
        }
        return \false;
    }
    private function refactorNode(\PhpParser\Node\Expr\Array_ $array) : \PhpParser\Node\Expr\FuncCall
    {
        $newItems = $this->createArrayItems($array);
        // Replace this array node with an `array_merge`
        return $this->createArrayMerge($array, $newItems);
    }
    /**
     * Iterate all array items:
     * 1. If they use the spread, remove it
     * 2. If not, make the item part of an accumulating array,
     *    to be added once the next spread is found, or at the end
     * @return ArrayItem[]
     */
    private function createArrayItems(\PhpParser\Node\Expr\Array_ $array) : array
    {
        $newItems = [];
        $accumulatedItems = [];
        foreach ($array->items as $position => $item) {
            if ($item !== null && $item->unpack) {
                // Spread operator found
                if (!$item->value instanceof \PhpParser\Node\Expr\Variable) {
                    // If it is a not variable, transform it to a variable
                    $item->value = $this->createVariableFromNonVariable($array, $item, $position);
                }
                if ($accumulatedItems !== []) {
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
        if ($accumulatedItems !== []) {
            $newItems[] = $this->createArrayItem($accumulatedItems);
        }
        return $newItems;
    }
    /**
     * @param (ArrayItem|null)[] $items
     */
    private function createArrayMerge(\PhpParser\Node\Expr\Array_ $array, array $items) : \PhpParser\Node\Expr\FuncCall
    {
        /** @var Scope $nodeScope */
        $nodeScope = $array->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_merge'), \array_map(function (\PhpParser\Node\Expr\ArrayItem $item) use($nodeScope) : Arg {
            if ($item !== null && $item->unpack) {
                // Do not unpack anymore
                $item->unpack = \false;
                return $this->createArgFromSpreadArrayItem($nodeScope, $item);
            }
            return new \PhpParser\Node\Arg($item);
        }, $items));
    }
    /**
     * If it is a variable, we add it directly
     * Otherwise it could be a function, method, ternary, traversable, etc
     * We must then first extract it into a variable,
     * as to invoke it only once and avoid potential bugs,
     * such as a method executing some side-effect
     * @param int|string $position
     */
    private function createVariableFromNonVariable(\PhpParser\Node\Expr\Array_ $array, \PhpParser\Node\Expr\ArrayItem $arrayItem, $position) : \PhpParser\Node\Expr\Variable
    {
        /** @var Scope $nodeScope */
        $nodeScope = $array->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // The variable name will be item0Unpacked, item1Unpacked, etc,
        // depending on their position.
        // The number can't be at the end of the var name, or it would
        // conflict with the counter (for if that name is already taken)
        $smartFileInfo = $this->file->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPath();
        $position = $this->lastPositionCurrentFile[$realPath] ?? $position;
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName($array, $nodeScope, 'item' . $position . 'Unpacked');
        if ($this->shouldIncrement) {
            $this->lastPositionCurrentFile[$realPath] = ++$position;
        }
        // Assign the value to the variable, and replace the element with the variable
        $newVariable = new \PhpParser\Node\Expr\Variable($variableName);
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Expr\Assign($newVariable, $arrayItem->value), $array);
        return $newVariable;
    }
    /**
     * @param array<ArrayItem|null> $items
     */
    private function createArrayItem(array $items) : \PhpParser\Node\Expr\ArrayItem
    {
        return new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Array_($items));
    }
    private function createArgFromSpreadArrayItem(\PHPStan\Analyser\Scope $nodeScope, \PhpParser\Node\Expr\ArrayItem $arrayItem) : \PhpParser\Node\Arg
    {
        // By now every item is a variable
        /** @var Variable $variable */
        $variable = $arrayItem->value;
        $variableName = $this->getName($variable) ?? '';
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
        if ($type !== null) {
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
