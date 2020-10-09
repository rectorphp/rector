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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
        foreach ($node->items as $position => $item) {
            if ($item !== null && $item->unpack) {
                // Spread operator found
                // If it is a variable, we add it directly
                // Otherwise transform it to a variable
                if (! $item->value instanceof Variable) {
                    $item->value = $this->createVariableFromNonVariable($node, $item, $position);
                }

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

    /**
      * If it is a variable, we add it directly
      * Otherwise it could be a function, method, ternary, traversable, etc
      * We must then first extract it into a variable,
      * as to invoke it only once and avoid potential bugs,
      * such as a method executing some side-effect
     * @param Array_ $node
     * @param int|string $position
     */
    private function createVariableFromNonVariable(Array_ $node, ArrayItem $item, $position): Variable
    {
        /** @var Scope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        // The variable name will be item0Unpacked, item1Unpacked, etc,
        // depending on their position.
        // The number can't be at the end of the var name, or it would
        // conflict with the counter (for if that name is already taken)
        $variableName = $this->variableNaming->resolveFromNodeWithScopeCountAndFallbackName(
            $node,
            $nodeScope,
            'item' . $position . 'Unpacked'
        );
        // Assign the value to the variable, and replace the element with the variable
        $newVariable = new Variable($variableName);
        $this->addNodeBeforeNode(new Assign($newVariable, $item->value), $node);
        return $newVariable;
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
