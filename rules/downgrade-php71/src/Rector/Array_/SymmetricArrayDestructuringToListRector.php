<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign && $this->areNodesEqual($node, $parentNode->var)) {
            return $this->processToList($node);
        }

        if ($parentNode instanceof Foreach_ && $this->areNodesEqual($node, $parentNode->valueVar)) {
            return $this->processToList($node);
        }

        return null;
    }

    private function processToList(Array_ $array): FuncCall
    {
        $args = [];
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            $args[] = new Arg($arrayItem->value);
        }

        return new FuncCall(new Name('list'), $args);
    }
}
