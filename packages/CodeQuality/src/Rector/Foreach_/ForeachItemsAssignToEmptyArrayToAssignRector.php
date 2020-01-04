<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\ForeachItemsAssignToEmptyArrayToAssignRectorTest
 */
final class ForeachItemsAssignToEmptyArrayToAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change foreach() items assign to empty array to direct assign', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($items)
    {
        $items2 = [];
        foreach ($items as $item) {
             $items2[] = $item;
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($items)
    {
        $items2 = [];
        $items2 = $items;
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $assignVariable = $this->matchAssignItemsOnlyForeachArrayVariable($node);
        if ($assignVariable === null) {
            return null;
        }

        $previousDeclaration = $this->findPreviousNodeUsage($node, $assignVariable);
        if ($previousDeclaration === null) {
            return null;
        }

        $previousDeclarationParentNode = $previousDeclaration->getAttribute(AttributeKey::PARENT_NODE);
        if (! $previousDeclarationParentNode instanceof Assign) {
            return null;
        }

        // must be empty array, othewise it will false override
        $defaultValue = $this->getValue($previousDeclarationParentNode->expr);
        if ($defaultValue !== []) {
            return null;
        }

        return new Assign($assignVariable, $node->expr);
    }

    private function matchAssignItemsOnlyForeachArrayVariable(Foreach_ $foreach): ?Expr
    {
        if (count($foreach->stmts) !== 1) {
            return null;
        }

        $onlyStatement = $foreach->stmts[0];
        if ($onlyStatement instanceof Expression) {
            $onlyStatement = $onlyStatement->expr;
        }

        if (! $onlyStatement instanceof Assign) {
            return null;
        }

        if (! $onlyStatement->var instanceof ArrayDimFetch) {
            return null;
        }

        if (! $this->areNodesEqual($foreach->valueVar, $onlyStatement->expr)) {
            return null;
        }

        return $onlyStatement->var->var;
    }

    private function findPreviousNodeUsage(Node $node, Expr $expr): ?Node
    {
        return $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use ($expr) {
            if ($node === $expr) {
                return false;
            }

            return $this->areNodesEqual($node, $expr);
        });
    }
}
