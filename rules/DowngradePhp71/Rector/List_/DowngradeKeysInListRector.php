<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\InflectorSingularResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\DowngradeKeysInListRectorTest
 */
final class DowngradeKeysInListRector extends AbstractRector
{
    /**
     * @var InflectorSingularResolver
     */
    private $inflectorSingularResolver;

    public function __construct(InflectorSingularResolver $inflectorSingularResolver)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
    }

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
        $id1 = $data[0]["id"];
        $name1 = $data[0]["name"];
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
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return null;
        }

        $parentExpression = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentExpression instanceof Node) {
            return null;
        }

        $assignExpression = $this->processExtractToItsOwnVariable($node, $parent, $parentExpression);

        if ($assignExpression !== []) {
            $this->addNodesBeforeNode($assignExpression, $node);
            $this->removeNode($parentExpression);

            return $node;
        }

        return null;
    }

    /**
     * @return mixed[][]|Expression[]
     */
    private function processExtractToItsOwnVariable(List_ $list, Node $parent, Node $parentExpression): array
    {
        $items = $list->items;
        $assignExpression = [];

        foreach ($items as $item) {
            if ($item instanceof ArrayItem && $item->key instanceof Expr) {
                if ($parentExpression instanceof Expression && $parent instanceof Assign && $parent->var === $list) {
                    $assignExpression[] = new Expression(
                        new Assign($item->value, new ArrayDimFetch($parent->expr, $item->key))
                    );
                }

                if ($parent instanceof Foreach_ && $parent->valueVar === $list) {
                    $assignExpression[] = $this->getExpressionFromForeachValue($parent, $item);
                }
            }
        }

        return $assignExpression;
    }

    /**
     * @return mixed[]
     */
    private function getExpressionFromForeachValue(Foreach_ $foreach): array
    {
        $newValueVar = $this->inflectorSingularResolver->resolve($this->getName($foreach->expr));
        $foreach->valueVar = new Variable($newValueVar);

        return [];
    }
}
