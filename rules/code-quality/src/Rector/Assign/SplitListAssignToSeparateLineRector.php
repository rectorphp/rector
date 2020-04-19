<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://mobile.twitter.com/ivanhoe011/status/1246376872931401728
 *
 * @see \Rector\CodeQuality\Tests\Rector\Assign\SplitListAssignToSeparateLineRector\SplitListAssignToSeparateLineRectorTest
 */
final class SplitListAssignToSeparateLineRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Splits [$a, $b] = [5, 10] scalar assign to standalone lines', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        [$a, $b] = [1, 2];
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $a = 1;
        $b = 2;
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
        foreach ($standaloneAssigns as $standaloneAssign) {
            $this->addNodeAfterNode($standaloneAssign, $node);
        }

        $this->removeNode($node);

        return $node;
    }

    private function shouldSkip($node): bool
    {
        if (! $node->var instanceof Array_ && ! $node->var instanceof List_) {
            return true;
        }

        if (! $node->expr instanceof Array_) {
            return true;
        }

        if (count($node->var->items) !== count($node->expr->items)) {
            return true;
        }

        // is value swap
        return $this->isValueSwap($node->var, $node->expr);
    }

    /**
     * @param Array_|List_ $node
     * @return Assign[]
     */
    private function createStandaloneAssigns(Node $node, Array_ $rightArray): array
    {
        $standaloneAssigns = [];
        foreach ($node->items as $key => $leftArrayItem) {
            $rightArrayItem = $rightArray->items[$key];

            $standaloneAssigns[] = new Assign($leftArrayItem->value, $rightArrayItem);
        }

        return $standaloneAssigns;
    }

    /**
     * @param Array_|List_ $node
     */
    private function getArrayItemsHash(Node $node): string
    {
        $arrayItemsHashes = [];
        foreach ($node->items as $arrayItem) {
            $arrayItemsHashes[] = $this->printWithoutComments($arrayItem);
        }

        sort($arrayItemsHashes);

        $arrayItemsHash = implode('', $arrayItemsHashes);

        return sha1($arrayItemsHash);
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
}
