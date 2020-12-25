<?php

declare(strict_types=1);

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
use Rector\Core\Rector\AbstractRector;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Traversable;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\Array_\DowngradeArraySpreadRector\DowngradeArraySpreadRectorTest
 */
final class DowngradeArraySpreadRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace array spread with array_merge function',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
        return $this->refactorNode($node);
    }

    private function shouldRefactor(Array_ $array): bool
    {
        // Check that any item in the array is the spread
        return array_filter($array->items, function (?ArrayItem $item): bool {
            if ($item === null) {
                return false;
            }
            return $item->unpack;
        }) !== [];
    }

    private function refactorNode(Array_ $array): Node
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
    private function createArrayItems(Array_ $array): array
    {
        $newItems = [];
        $accumulatedItems = [];
        /** @var Scope $nodeScope */
        $nodeScope = $array->getAttribute(AttributeKey::SCOPE);
        foreach ($array->items as $position => $item) {
            if ($item !== null && $item->unpack) {
                // Spread operator found
                if (! $item->value instanceof Variable) {
                    // If it is a not variable, transform it to a variable
                    // Keep the original type, will be needed later
                    $item->setAttribute(AttributeKey::ORIGINAL_TYPE, $nodeScope->getType($item->value));
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
     * @see https://wiki.php.net/rfc/spread_operator_for_array
     * @param (ArrayItem|null)[] $items
     */
    private function createArrayMerge(Array_ $array, array $items): FuncCall
    {
        /** @var Scope $nodeScope */
        $nodeScope = $array->getAttribute(AttributeKey::SCOPE);
        return new FuncCall(new Name('array_merge'), array_map(function (ArrayItem $item) use ($nodeScope): Arg {
            if ($item !== null && $item->unpack) {
                // Do not unpack anymore
                $item->unpack = false;
                return $this->createArgFromSpreadArrayItem($nodeScope, $item);
            }
            return new Arg($item);
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
    private function createVariableFromNonVariable(Array_ $array, ArrayItem $arrayItem, $position): Variable
    {
        /** @var Scope $nodeScope */
        $nodeScope = $array->getAttribute(AttributeKey::SCOPE);
        // The variable name will be item0Unpacked, item1Unpacked, etc,
        // depending on their position.
        // The number can't be at the end of the var name, or it would
        // conflict with the counter (for if that name is already taken)
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName(
            $array,
            $nodeScope,
            'item' . $position . 'Unpacked'
        );
        // Assign the value to the variable, and replace the element with the variable
        $newVariable = new Variable($variableName);
        $this->addNodeBeforeNode(new Assign($newVariable, $arrayItem->value), $array);
        return $newVariable;
    }

    /**
     * @param (ArrayItem|null)[] $items
     */
    private function createArrayItem(array $items): ArrayItem
    {
        return new ArrayItem(new Array_($items));
    }

    private function createArgFromSpreadArrayItem(Scope $nodeScope, ArrayItem $arrayItem): Arg
    {
        // By now every item is a variable
        /** @var Variable $variable */
        $variable = $arrayItem->value;
        $variableName = $this->getName($variable) ?? '';
        // If the variable is not in scope, it's one we just added.
        // Then get the type from the attribute
        $type = $nodeScope->hasVariableType($variableName)
            ->yes() ? $nodeScope->getVariableType($variableName) : $arrayItem->getAttribute(
                AttributeKey::ORIGINAL_TYPE
            );

        $iteratorToArrayFuncCall = new FuncCall(new Name('iterator_to_array'), [new Arg($arrayItem)]);

        if ($type !== null) {
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
        }

        // Print a ternary, handling either an array or an iterator
        $inArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($arrayItem)]);
        return new Arg(new Ternary($inArrayFuncCall, $arrayItem, $iteratorToArrayFuncCall));
    }

    /**
     * Iterables: either objects declaring the interface Traversable,
     * or the pseudo-type iterable
     */
    private function isIterableType(Type $type): bool
    {
        return $type instanceof IterableType || ($type instanceof ObjectType && is_a(
            $type->getClassName(),
            Traversable::class,
            true
        ));
    }
}
