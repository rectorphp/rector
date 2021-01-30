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
 * @see https://mobile.twitter.com/ivanhoe011/status/1246376872931401728
 *
 * @see \Rector\CodeQuality\Tests\Rector\Assign\SplitListAssignToSeparateLineRector\SplitListAssignToSeparateLineRectorTest
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

            ]);
    }

    /**
     * @return string[]
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

        if (! $assign->expr instanceof Array_) {
            return true;
        }

        if (count($assign->var->items) !== count($assign->expr->items)) {
            return true;
        }

        // is value swap
        return $this->isValueSwap($assign->var, $assign->expr);
    }

    /**
     * @param Array_|List_ $node
     * @return Assign[]
     */
    private function createStandaloneAssigns(Node $node, Array_ $rightArray): array
    {
        $standaloneAssigns = [];
        foreach ($node->items as $key => $leftArrayItem) {
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

    /**
     * @param Array_|List_ $firstArray
     * @param Array_|List_ $secondArray
     */
    private function isValueSwap($firstArray, $secondArray): bool
    {
        $firstArrayItemsHash = $this->getArrayItemsHash($firstArray);
        $secondArrayItemsHash = $this->getArrayItemsHash($secondArray);

        return $firstArrayItemsHash === $secondArrayItemsHash;
    }

    /**
     * @param Array_|List_ $node
     */
    private function getArrayItemsHash(Node $node): string
    {
        $arrayItemsHashes = [];
        foreach ($node->items as $arrayItem) {
            $arrayItemsHashes[] = $this->betterStandardPrinter->printWithoutComments($arrayItem);
        }

        sort($arrayItemsHashes);

        $arrayItemsHash = implode('', $arrayItemsHashes);

        return sha1($arrayItemsHash);
    }
}
