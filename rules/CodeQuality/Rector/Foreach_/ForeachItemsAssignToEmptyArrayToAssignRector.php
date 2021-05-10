<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Foreach_;
use Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\ForeachItemsAssignToEmptyArrayToAssignRectorTest
 */
final class ForeachItemsAssignToEmptyArrayToAssignRector extends AbstractRector
{
    public function __construct(
        private NodeUsageFinder $nodeUsageFinder,
        private ForeachAnalyzer $foreachAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change foreach() items assign to empty array to direct assign',
            [
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
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
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
        $assignVariable = $this->foreachAnalyzer->matchAssignItemsOnlyForeachArrayVariable($node);
        if (! $assignVariable instanceof Expr) {
            return null;
        }

        if ($this->shouldSkipAsPartOfNestedForeach($node)) {
            return null;
        }

        $previousDeclaration = $this->nodeUsageFinder->findPreviousForeachNodeUsage($node, $assignVariable);
        if (! $previousDeclaration instanceof Node) {
            return null;
        }

        $previousDeclarationParentNode = $previousDeclaration->getAttribute(AttributeKey::PARENT_NODE);
        if (! $previousDeclarationParentNode instanceof Assign) {
            return null;
        }

        // must be empty array, otherwise it will false override
        $defaultValue = $this->valueResolver->getValue($previousDeclarationParentNode->expr);
        if ($defaultValue !== []) {
            return null;
        }

        return new Assign($assignVariable, $node->expr);
    }

    private function shouldSkipAsPartOfNestedForeach(Foreach_ $foreach): bool
    {
        $foreachParent = $this->betterNodeFinder->findParentType($foreach, Foreach_::class);
        return $foreachParent !== null;
    }
}
