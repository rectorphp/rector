<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\Expression;
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Splits `[$a, $b] = [5, 10]` scalar assign to standalone lines', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if ($this->shouldSkipAssign($assign)) {
            return null;
        }
        /** @var Array_|List_ $leftArray */
        $leftArray = $assign->var;
        /** @var Array_ $rightArray */
        $rightArray = $assign->expr;
        return $this->createStandaloneAssignExpressions($leftArray, $rightArray);
    }
    private function shouldSkipAssign(Assign $assign) : bool
    {
        if (!$assign->var instanceof Array_ && !$assign->var instanceof List_) {
            return \true;
        }
        $assignExpr = $assign->expr;
        if (!$assignExpr instanceof Array_) {
            return \true;
        }
        if (\count($assign->var->items) !== \count($assignExpr->items)) {
            return \true;
        }
        // is value swap
        return $this->isValueSwap($assign->var, $assignExpr);
    }
    /**
     * @return Expression[]
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\List_ $expr
     */
    private function createStandaloneAssignExpressions($expr, Array_ $rightArray) : array
    {
        $standaloneAssignExpresssions = [];
        foreach ($expr->items as $key => $leftArrayItem) {
            if ($leftArrayItem === null) {
                continue;
            }
            $rightArrayItem = $rightArray->items[$key];
            if (!$rightArrayItem instanceof ArrayItem) {
                continue;
            }
            $assign = new Assign($leftArrayItem->value, $rightArrayItem);
            $standaloneAssignExpresssions[] = new Expression($assign);
        }
        return $standaloneAssignExpresssions;
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\List_ $expr
     */
    private function isValueSwap($expr, Array_ $secondArray) : bool
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
