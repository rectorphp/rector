<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Rector\List_;

use PhpParser\Node;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\AssignRef;
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
        return new RectorDefinition('Convert `list()` reference assignment to PHP 7.2 code: `list($a, &$b) = $array;` => `list($a, $b) = $array; $b =& $array[1];`', [
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
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [List_::class];
    }

    /**
     * @param List_ $node
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
        // dump($assignExpr);die;
        // Their position is kept in the array
        $newNodes = [];
        foreach ($itemsByRef as $position => $itemByRef) {
            $node->items[$position]->byRef = false;
            // Assign the value by reference on a new assignment
            /** @var Variable */
            $itemVariable = $itemByRef->value;
            $assignVariable = new Variable($itemVariable->name);
            $newNodes[] = $this->createAssignRefWithArrayDimFetch($assignVariable, $exprVariable, $position);
        }
        $this->addNodesAfterNode($newNodes, $node);
        return $node;
    }

    /**
     * @param List_ $node
     */
    private function shouldRefactor(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        // Check it follows `list(...) = $foo`
        if ($parentNode instanceof Assign && $parentNode->expr instanceof Variable && $parentNode->var === $node) {
            // There must be at least one param by reference
            return !empty($this->getItemsByRef($node));
        }

        return false;
    }

    /**
     * @param List_ $node
     * @return ArrayItem[]|null[] It maintains the same keys from the original
     */
    private function getItemsByRef(Node $node): array
    {
        return array_filter(
            $node->items,
            function (ArrayItem $item): bool {
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
