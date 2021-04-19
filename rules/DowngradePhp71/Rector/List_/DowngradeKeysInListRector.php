<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\List_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

/**
 * @see \Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\DowngradeKeysInListRectorTest
 */
final class DowngradeKeysInListRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [List_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Extract keys in list to its own variable assignment',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];

        list("id" => $id1, "name" => $name1) = $data[0];
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];

        $id1 = $data[0]['id'];
        $name1 = $data[0]['name'];
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param List_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->verify($node);

        $assign = $node->getAttribute(AttributeKey::PARENT_NODE);
        $items = $node->items;

        $countToBeChanged = 0;
        foreach ($items as $item) {
            if ($item->key instanceof String_) {
                $this->processExtractToItsOwnVariable($node, $item, $assign);
                ++$countToBeChanged;
            }
        }

        $parentExpression = $assign->getAttribute(AttributeKey::PARENT_NODE);

        if ($countToBeChanged === count($items)) {
            $this->removeNode($parentExpression);
        }

        return null;
    }

    private function processExtractToItsOwnVariable(List_ $list, ArrayItem $arrayItem, Assign $assign): void
    {
        $assignExpression = new Expression(
            new Assign(
                $arrayItem->value,
                new ArrayDimFetch($assign->expr, $arrayItem->key)
            )
        );
        $this->addNodeBeforeNode($assignExpression, $list);
    }

    private function verify(List_ $list): void
    {
        $parent = $list->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Assign || $parent->var !== $list) {
            throw new ShouldNotHappenException();
        }

        $expression = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $expression instanceof Expression) {
            throw new ShouldNotHappenException();
        }
    }
}
