<?php

declare (strict_types=1);
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
final class SplitListAssignToSeparateLineRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Splits `[$a, $b] = [5, 10]` scalar assign to standalone lines', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        [$a, $b] = [1, 2];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $a = 1;
        $b = 2;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var Array_|List_ $leftArray */
        $leftArray = $node->var;
        /** @var Array_ $rightArray */
        $rightArray = $node->expr;
        $standaloneAssigns = $this->createStandaloneAssigns($leftArray, $rightArray);
        $this->nodesToAddCollector->addNodesAfterNode($standaloneAssigns, $node);
        $this->removeNode($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        if (!$assign->var instanceof \PhpParser\Node\Expr\Array_ && !$assign->var instanceof \PhpParser\Node\Expr\List_) {
            return \true;
        }
        $assignExpr = $assign->expr;
        if (!$assignExpr instanceof \PhpParser\Node\Expr\Array_) {
            return \true;
        }
        if (\count($assign->var->items) !== \count($assignExpr->items)) {
            return \true;
        }
        // is value swap
        return $this->isValueSwap($assign->var, $assignExpr);
    }
    /**
     * @return Assign[]
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\List_ $expr
     */
    private function createStandaloneAssigns($expr, \PhpParser\Node\Expr\Array_ $rightArray) : array
    {
        $standaloneAssigns = [];
        foreach ($expr->items as $key => $leftArrayItem) {
            if ($leftArrayItem === null) {
                continue;
            }
            $rightArrayItem = $rightArray->items[$key];
            if (!$rightArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $standaloneAssigns[] = new \PhpParser\Node\Expr\Assign($leftArrayItem->value, $rightArrayItem);
        }
        return $standaloneAssigns;
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\List_ $expr
     */
    private function isValueSwap($expr, \PhpParser\Node\Expr\Array_ $secondArray) : bool
    {
        $firstArrayItemsHash = $this->getArrayItemsHash($expr);
        $secondArrayItemsHash = $this->getArrayItemsHash($secondArray);
        return $firstArrayItemsHash === $secondArrayItemsHash;
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\List_ $node
     */
    private function getArrayItemsHash($node) : string
    {
        $arrayItemsHashes = [];
        foreach ($node->items as $arrayItem) {
            $arrayItemsHashes[] = $this->nodeComparator->printWithoutComments($arrayItem);
        }
        \sort($arrayItemsHashes);
        $arrayItemsHash = \implode('', $arrayItemsHashes);
        return \sha1($arrayItemsHash);
    }
}
