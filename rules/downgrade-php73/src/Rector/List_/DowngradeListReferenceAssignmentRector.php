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
        $itemsByRef = $this->getItemsByRef($node);
        /** @var Assign */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        /** @var Variable */
        $exprVariable = $parentNode->expr;
        // Their position is kept in the array
        $newNodes = [];
        foreach ($itemsByRef as $position => $itemByRef) {
            // Change to not assign by reference in the present node
            $itemByRef->byRef = false;
            // Assign the value by reference on a new assignment
            /** @var Variable */
            $itemVariable = $itemByRef->value;
            $assignVariable = new Variable($itemVariable->name);
            // Access the array under the key, if provided, or the position otherwise
            $key = $position;
            if ($itemByRef->key !== null && $itemByRef->key instanceof String_) {
                $key = $itemByRef->key->value;
            }
            $newNodes[] = $this->createAssignRefWithArrayDimFetch($assignVariable, $exprVariable, $key);
        }
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
            // There must be at least one param by reference
            return count($this->getItemsByRef($node)) > 0;
        }

        return false;
    }

    /**
     * @param List_|Array_ $node
     * @return ArrayItem[] It maintains the same keys from the original
     */
    private function getItemsByRef(Node $node): array
    {
        /** @var ArrayItem[] */
        return array_filter(
            $node->items,
            /**
             * @var ArrayItem|null $item
             */
            function ($item): bool {
                if ($item === null) {
                    return false;
                }
                return $item->value instanceof Variable && $item->byRef;
            }
        );
    }

    /**
     * @param string|int $dimValue
     */
    private function createAssignRefWithArrayDimFetch(
        Variable $assignVariable,
        Variable $exprVariable,
        $dimValue
    ): AssignRef {
        $dim = BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new ArrayDimFetch($exprVariable, $dim);
        return new AssignRef($assignVariable, $arrayDimFetch);
    }
}
