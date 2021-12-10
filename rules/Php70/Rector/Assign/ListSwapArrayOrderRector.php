<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 * @see \Rector\Tests\Php70\Rector\Assign\ListSwapArrayOrderRector\ListSwapArrayOrderRectorTest
 */
final class ListSwapArrayOrderRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('list() assigns variables in reverse order - relevant in array assign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('list($a[], $a[]) = [1, 2];', 'list($a[], $a[]) = array_reverse([1, 2]);')]);
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
        if ($this->shouldSkipAssign($node)) {
            return null;
        }
        /** @var List_ $list */
        $list = $node->var;
        $printedVariables = [];
        foreach ($list->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($arrayItem->value instanceof \PhpParser\Node\Expr\ArrayDimFetch && $arrayItem->value->dim === null) {
                $printedVariables[] = $this->print($arrayItem->value->var);
            } else {
                return null;
            }
        }
        // relevant only in 1 variable type
        $uniqueVariables = \array_unique($printedVariables);
        if (\count($uniqueVariables) !== 1) {
            return null;
        }
        // wrap with array_reverse, to reflect reverse assign order in left
        $node->expr = $this->nodeFactory->createFuncCall('array_reverse', [$node->expr]);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::LIST_SWAP_ORDER;
    }
    private function shouldSkipAssign(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        if (!$assign->var instanceof \PhpParser\Node\Expr\List_) {
            return \true;
        }
        // already converted
        return $assign->expr instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($assign->expr, 'array_reverse');
    }
}
