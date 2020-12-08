<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Array_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use PhpParser\Node\Arg;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\Array_\SymmetricArrayDestructuringToListRector\SymmetricArrayDestructuringToListRectorTest
 */
final class SymmetricArrayDestructuringToListRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade Symmetric array destructuring to list() function', [
            new CodeSample('[$id1, $name1] = $data;', 'list($id1, $name1) = $data;'),
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
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Assign) {
            return $this->processToListInAssign($node, $parent);
        }

        return null;
    }

    private function processToListInAssign(Array_ $array, Assign $assign): ?FuncCall
    {
        if ($this->areNodesEqual($assign->var, $array)) {
            $args = [];
            foreach ($array->items as $item) {
                $args[] = new Arg($item->value);
            }

            return new FuncCall(new Name('list'), $args);
        }

        return null;
    }
}
