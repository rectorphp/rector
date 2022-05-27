<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/aLf96
 * @changelog https://3v4l.org/2r26K
 * @changelog https://3v4l.org/anks3
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\ArrayMergeOfNonArraysToSimpleArrayRectorTest
 */
final class ArrayMergeOfNonArraysToSimpleArrayRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array_merge of non arrays to array directly', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return array_merge([$value], [$value2]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        $value = 5;
        $value2 = 10;

        return [$value, $value2];
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'array_merge')) {
            return null;
        }
        $array = new Array_();
        $isAssigned = \false;
        foreach ($node->args as $arg) {
            // found non Arg? return early
            if (!$arg instanceof Arg) {
                return null;
            }
            $nestedArrayItem = $arg->value;
            if (!$nestedArrayItem instanceof Array_) {
                return null;
            }
            foreach ($nestedArrayItem->items as $nestedArrayItemItem) {
                if ($nestedArrayItemItem === null) {
                    continue;
                }
                $array->items[] = new ArrayItem($nestedArrayItemItem->value, $nestedArrayItemItem->key);
                $isAssigned = \true;
            }
        }
        if (!$isAssigned) {
            return null;
        }
        return $array;
    }
}
