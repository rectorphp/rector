<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use Rector\CodeQuality\NodeAnalyzer\ForeachNodeAnalyzer;
use Rector\Core\NodeFinder\NodeUsageFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\ForeachItemsAssignToEmptyArrayToAssignRectorTest
 */
final class ForeachItemsAssignToEmptyArrayToAssignRector extends AbstractRector
{
    /**
     * @var NodeUsageFinder
     */
    private $nodeUsageFinder;

    /**
     * @var ForeachNodeAnalyzer
     */
    private $foreachNodeAnalyzer;

    public function __construct(NodeUsageFinder $nodeUsageFinder, ForeachNodeAnalyzer $foreachNodeAnalyzer)
    {
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->foreachNodeAnalyzer = $foreachNodeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change foreach() items assign to empty array to direct assign', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        foreach ($items as $item) {
             $collectedItems[] = $item;
        }
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        $collectedItems = $items;
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
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $assignVariable = $this->foreachNodeAnalyzer->matchAssignItemsOnlyForeachArrayVariable($node);
        if ($assignVariable === null) {
            return null;
        }

        /** @var Scope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $previousDeclaration = $this->nodeUsageFinder->findPreviousForeachNodeUsage($node, $assignVariable);
        if ($previousDeclaration === null) {
            return null;
        }

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

    private function shouldSkipAsPartOfNestedForeach(Foreach_ $foreach): bool
    {
        /** @var Variable|null $foreachedVariable */
        $foreachedVariable = $this->betterNodeFinder->findFirstInstanceOf($foreach->expr, Variable::class);
        if ($foreachedVariable === null) {
            return false;
        }

        $previousForeachVariableUsage = $this->nodeUsageFinder->findPreviousForeachNodeUsage(
            $foreach,
            $foreachedVariable
        );

        if ($previousForeachVariableUsage === null) {
            return false;
        }

        $parent = $previousForeachVariableUsage->getAttribute(AttributeKey::PARENT_NODE);

        /** @var Foreach_ $previousForeachVariableUsageParentNode */
        $previousForeachVariableUsageParentNode = $parent;

        return $this->areNodesEqual($previousForeachVariableUsageParentNode->valueVar, $foreachedVariable);
    }
}
