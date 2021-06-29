<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://mobile.twitter.com/ivanhoe011/status/1246376872931401728
 *
 * @see \Rector\Tests\CodeQuality\Rector\Assign\SplitListAssignToSeparateLineRector\SplitListAssignToSeparateLineRectorTest
 */
final class SplitListAssignToSeparateLineRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Splits `[$a, $b] = [5, 10]` scalar assign to standalone lines',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        [$a, $b] = [1, 2];
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $a = 1;
        $b = 2;
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Array_|List_ $leftArray */
        $leftArray = $node->var;

        /** @var Array_ $rightArray */
        $rightArray = $node->expr;

        $standaloneAssigns = $this->createStandaloneAssigns($leftArray, $rightArray);
        $this->addNodesAfterNode($standaloneAssigns, $node);

        $this->removeNode($node);

        return $node;
    }

    private function shouldSkip(Assign $assign): bool
    {
        if (! $assign->var instanceof Array_ && ! $assign->var instanceof List_) {
            return true;
        }

        $assignExpr = $assign->expr;
        if (! $assignExpr instanceof Array_) {
            return true;
        }

        if (count($assign->var->items) !== count($assignExpr->items)) {
            return true;
        }

        // is value swap
        return $this->isValueSwap($assign->var, $assignExpr);
    }

    /**
     * @return Assign[]
     */
    private function createStandaloneAssigns(Array_ | List_ $expr, Array_ $rightArray): array
    {
        $standaloneAssigns = [];
        foreach ($expr->items as $key => $leftArrayItem) {
            if ($leftArrayItem === null) {
                continue;
            }

            $rightArrayItem = $rightArray->items[$key];

            if (! $rightArrayItem instanceof ArrayItem) {
                continue;
            }

            $standaloneAssigns[] = new Assign($leftArrayItem->value, $rightArrayItem);
        }

        return $standaloneAssigns;
    }

    private function isValueSwap(Array_ | List_ $expr, Array_ $secondArray): bool
    {
        $firstArrayItemsHash = $this->getArrayItemsHash($expr);
        $secondArrayItemsHash = $this->getArrayItemsHash($secondArray);

        return $firstArrayItemsHash === $secondArrayItemsHash;
    }

    private function getArrayItemsHash(Array_ | List_ $node): string
    {
        $arrayItemsHashes = [];
        foreach ($node->items as $arrayItem) {
            $arrayItemsHashes[] = $this->nodeComparator->printWithoutComments($arrayItem);
        }

        sort($arrayItemsHashes);

        $arrayItemsHash = implode('', $arrayItemsHashes);

        return sha1($arrayItemsHash);
    }
}
