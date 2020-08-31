<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\ForeachItemsAssignToEmptyArrayToAssignRectorTest
 */
final class ForeachItemsAssignToEmptyArrayToAssignRector extends AbstractRector
{
    /**
     * @var ScopeAwareNodeFinder
     */
    private $scopeAwareNodeFinder;

    public function __construct(ScopeAwareNodeFinder $scopeAwareNodeFinder)
    {
        $this->scopeAwareNodeFinder = $scopeAwareNodeFinder;
    }

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

        // covers https://github.com/Symplify/Symplify/pull/1733/checks?check_run_id=379368887#step:5:64
        if ($this->shouldSkipAsPartOfNestedForeach($node)) {
            return null;
        }

        $previousDeclarationParentNode = $previousDeclaration->getAttribute(AttributeKey::PARENT_NODE);
        if (! $previousDeclarationParentNode instanceof Assign) {
            return null;
        }

        // must be empty array, otherwise it will false override
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

        if ($onlyStatement->var->dim !== null) {
            return null;
        }

        if (! $this->areNodesEqual($foreach->valueVar, $onlyStatement->expr)) {
            return null;
        }

        return $onlyStatement->var->var;
    }

    private function findPreviousNodeUsage(Node $node, Expr $expr): ?Node
    {
        return $this->scopeAwareNodeFinder->findParent($node, function (Node $node) use ($expr): bool {
            if ($node === $expr) {
                return false;
            }

            return $this->areNodesEqual($node, $expr);
        }, [Foreach_::class]);
    }

    private function shouldSkipAsPartOfNestedForeach(Foreach_ $foreach): bool
    {
        $previousForeachVariableUsage = $this->findPreviousNodeUsageInForeach($foreach, $foreach->expr);
        if ($previousForeachVariableUsage === null) {
            return false;
        }

        /** @var Foreach_ $previousForeachVariableUsageParentNode */
        $previousForeachVariableUsageParentNode = $previousForeachVariableUsage->getAttribute(
            AttributeKey::PARENT_NODE
        );

        return $this->areNodesEqual($previousForeachVariableUsageParentNode->valueVar, $foreach->expr);
    }

    private function findPreviousNodeUsageInForeach(Node $node, Expr $expr): ?Node
    {
        return $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use ($expr): bool {
            if ($node === $expr) {
                return false;
            }

            if (! $this->areNodesEqual($node, $expr)) {
                return false;
            }

            return $node->getAttribute(AttributeKey::PARENT_NODE) instanceof Foreach_;
        });
    }
}
